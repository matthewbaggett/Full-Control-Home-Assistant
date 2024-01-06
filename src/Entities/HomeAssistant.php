<?php

declare(strict_types=1);

namespace FullControl\Entities;

use FullControl\FullControl;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Tag\TaggedValue;

class HomeAssistant extends AbstractEntity
{
    public function __construct(
        readonly private string $name,
    ) {
    }

    public function emit(LoggerInterface $logger, FullControl $fc): void
    {
        $fc->addYaml([
            'configuration.yaml' => [
                'homeassistant' => [
                    'name'           => $this->name,
                    'auth_providers' => [
                        'type' => 'homeassistant',
                    ],
                ],
                'recorder' => [
                    'purge_keep_days' => 365 * 100,
                    'auto_purge'      => false,
                    'db_url'          => new TaggedValue('secret','db_url'),
                    'db_retry_wait'   => 15,
                ],
            ],
            'secrets.yaml' => [
                'db_url' => http_build_url([
                    'scheme' => $fc->getEnvironment()->get('DATABASE_TYPE'),
                    'host'   => $fc->getEnvironment()->get('DATABASE_HOST'),
                    'port'   => $fc->getEnvironment()->get('DATABASE_PORT'),
                    'user'   => $fc->getEnvironment()->get('DATABASE_USERNAME'),
                    'pass'   => $fc->getEnvironment()->get('DATABASE_PASSWORD'),
                    'path'   => sprintf('/%s', $fc->getEnvironment()->get('DATABASE_DATABASE', 'homeassistant')),
                ]),
            ],
        ]);
        parent::emit($logger, $fc);
    }
}
