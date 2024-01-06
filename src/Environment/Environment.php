<?php

declare(strict_types=1);

namespace FullControl\Environment;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class Environment
{
    private array $store = [];

    public function __construct(
        protected LoggerInterface $logger
    ) {
        $this->setMany(array_merge($_ENV, $_SERVER));
    }

    public function loadEnv(string $filename): static
    {
        if (file_exists($filename)) {
            $this->logger->info(sprintf('Environment: Loading env file %s', $filename));

            return $this->setMany(parse_ini_file($filename));
        }
        $this->logger->debug(sprintf('Environment: Skipping missing env file %s', $filename));

        return $this;
    }

    public function loadYaml(string $filename): static
    {
        if (file_exists($filename)) {
            $this->logger->info(sprintf('Environment: Loading yaml file %s', $filename));

            return $this->setMany(Yaml::parseFile($filename));
        }
        $this->logger->debug(sprintf('Environment: Skipping missing env file %s', $filename));

        return $this;
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->has($key) ? $this->store[strtoupper($key)] : $default;
    }

    public function getBool(string $key): bool
    {
        return filter_var($this->get($key), FILTER_VALIDATE_BOOLEAN);
    }

    public function set(string $key, mixed $value): static
    {
        return $this->setMany([$key => $value]);
    }

    public function setBool(string $key, bool $value): static
    {
        return $this->set($key, $value);
    }

    public function has(string $key): bool
    {
        return isset($this->store[strtoupper($key)]);
    }

    public function setMany(array $getOpts): static
    {
        foreach ($getOpts as $key => $value) {
            $this->store[strtoupper($key)] = $value;
        }
        ksort($this->store);

        return $this;
    }

    public function __toArray(): array
    {
        return $this->store;
    }
}
