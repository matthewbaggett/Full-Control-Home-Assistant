<?php

declare(strict_types=1);

namespace FullControl;

use FullControl\Entities\AbstractEntity;
use FullControl\Environment\Environment;
use Garden\Cli\Cli;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Monolog\Processor\PsrLogMessageProcessor;
use SebastianBergmann\Timer\Timer;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Level;
use Symfony\Component\Yaml\Yaml;

class FullControl
{
    protected Logger $logger;

    private array $yaml    = [];
    private array $json    = [];
    protected Filesystem $storeYamls;
    protected Filesystem $storeJsons;
    protected Environment $environment;

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function addYaml($yaml): static
    {
        $this->yaml = array_merge($this->yaml, $yaml);

        return $this;
    }

    public function addJson($json): static
    {
        $this->json = array_merge($this->json, $json);

        return $this;
    }

    public function __construct()
    {
        // Configure logging
        $this->logger = new Logger('FullControl');
        $this->logger->pushHandler(new StreamHandler('/var/log/fullcontrol.log', Level::Debug));
        $stdout = new StreamHandler('php://stdout', Level::Debug);
        $stdout->setFormatter(new ColoredLineFormatter(
            format: "%channel%: %level_name%: %message% \n",
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: true,
        ));
        $this->logger->pushHandler($stdout);
        $this->logger->pushProcessor(new PsrLogMessageProcessor());

        // Parse environment
        $this->environment = (new Environment($this->logger));

        $cli = new Cli();
        $cli->description('Start the Full Control Home Assistant application')
            ->opt(
                name: 'workdir:w',
                description: 'Specify the working directory',
                required: false,
                type: 'string',
            )
            ->opt(
                name: 'config:c',
                description: 'Specify configuration files to load, can be stated multiple times',
                required: false,
                type: 'string[]',
            )
            ->opt(
                name: 'dry',
                description: 'Do not actually emit the configuration',
                required: false,
                type: 'bool',
            )
        ;

        // Parse cli commands
        global $argv;
        $args = $cli->parse($argv, true);
        $this->environment->setMany($args->getOpts());

        // Parse --workdir
        if ($args->hasOpt('workdir')) {
            if (chdir($args->getOpt('workdir'))) {
                $this->logger->debug(sprintf('Changed working directory to %s', $args->getOpt('workdir')));
            } else {
                $this->logger->critical(sprintf('Could not change working directory to %s', $args->getOpt('workdir')));

                exit(1);
            }
        }
        // Parse --config[]
        if ($args->hasOpt('config')) {
            $this->environment->set('FULLCONTROL_CONFIG', $args->getOpt('config'));
        }

        // Populate environment from files while we can and we're vaugely cognizant.
        $this->environment
            ->loadEnv(sprintf('%s/fullcontrol.env', $this->environment->get('PWD')))
            ->loadEnv(sprintf('%s/fullcontrol.env', dirname($this->environment->get('PHP_SELF'))))
            ->loadEnv(sprintf('%s/fullcontrol.env', getcwd()))
        ;

        // Configure filesystems
        $this->storeYamls   = new Filesystem(new LocalFilesystemAdapter($this->environment->get('FULLCONTROL_YAML_DIR')));
        $this->storeJsons   = new Filesystem(new LocalFilesystemAdapter($this->environment->get('FULLCONTROL_JSON_DIR')));
    }

    public function run(): void
    {
        array_map(function ($filename): void { $this->eval($filename); }, $this->environment->get('FULLCONTROL_CONFIG', []));
        array_map(function ($filename): void { $this->eval($filename); }, glob('*.php'));

        $this->emit();
    }

    protected function eval(string $filename): void
    {
        if (!file_exists($filename)) {
            $this->logger->critical(sprintf('Could not load %s', $filename));

            exit(1);
        }
        $fc     = $this;
        $logger = $this->logger->withName($filename);
        call_user_func(function () use ($filename, $fc, $logger): void {
            $logger->debug('Loading {filename}...', ['filename' => $filename]);
            $timer = new Timer();
            $timer->start();

            require_once $filename;
            foreach (get_defined_vars() as $name => $value) {
                if ($value instanceof AbstractEntity) {
                    $value->emit($logger, $fc);
                }
            }
            $duration = $timer->stop();

            $logger->debug(
                'Completed loading {filename} in {runtime_ms}ms.',
                [
                    'filename'   => $filename,
                    'runtime_ms' => number_format($duration->asMilliseconds(), 3),
                ]
            );
        });
    }

    protected function emit(): void
    {
        array_walk($this->yaml, function ($data, $file): void {
            $encoded = Yaml::dump($data, inline: 10, indent: 2, flags: Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
            if (!$this->environment->getBool('DRY')) {
                $this->logger->info('Emitting {file} ({bytes} bytes)', ['file' => $file, 'bytes' => strlen($encoded)]);
                $this->storeYamls->write($file, $encoded);
            } else {
                $this->logger->warning('Not emitting {file} ({bytes} bytes), as we are in dry run mode (--dry)', ['file' => $file, 'bytes' => strlen($encoded)]);
            }
        });
    }
}
