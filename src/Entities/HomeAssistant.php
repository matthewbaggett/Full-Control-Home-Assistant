<?php

declare(strict_types=1);

namespace FullControl\Entities;

use FullControl\FullControl;
use FullControl\Storage\ConfigEntry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Tag\TaggedValue;

class HomeAssistant extends AbstractEntity
{
    public function __construct(
        readonly private string $name,
        readonly private string $latitude,
        readonly private string $longitude,
        readonly private string $timezone,
        readonly private string $currency,
        readonly private string $country,
        readonly private string $language,
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
                    'db_url'          => new TaggedValue('secret', 'db_url'),
                    'db_retry_wait'   => 15,
                ],
                'logger' => [
                    'default' => 'info',
                ],
                'default_config' => [],
                'automation'     => new TaggedValue('include', 'automations.yaml'),
                'script'         => new TaggedValue('include', 'scripts.yaml'),
                'scene'          => new TaggedValue('include', 'scenes.yaml'),
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
            'scripts.yaml'     => [],
            'automations.yaml' => [],
            'scenes.yaml'      => [],
        ]);

        $fc->addJson([
            // pretty sure this is to do with the "do you want us to track your shit" nag screen
            'core.analytics' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'core.analytics',
                'data'          => [
                    'onboarded'   => true,
                    'preferences' => [], // Pretty sure this empty array is how we say "hell no"
                    'uuid'        => null,
                ],
            ],

            // This is the household "areas" stuff. We need to stub out an empty array here so that
            // Entity\Areas can populate it.
            'core.area_registry' => [
                'version'       => 1,
                'minor_version' => 3,
                'key'           => 'core.area_registry',
                'data'          => [],
            ],

            // Where are we, when are we, where on the internet are we, welke taal spreken wij, etc
            'core.config' => [
                'version'       => 1,
                'minor_version' => 3,
                'key'           => 'core.config',
                'data'          => [
                    'latitude'       => $this->latitude  ?? 0,
                    'longitude'      => $this->longitude ?? 0,
                    'elevation'      => 0,
                    'unit_system_v2' => 'metric',
                    'location_name'  => 'Home',
                    'time_zone'      => $this->timezone ?? 'Europe/London',
                    'external_url'   => null, // @todo
                    'internal_url'   => null, // @todo
                    'currency'       => $this->currency ?? 'USD',
                    'country'        => $this->country  ?? 'NL',
                    'language'       => $this->language ?? 'en',
                ],
            ],

            // Entries.. We're going to spend a lot of time spelunking here..
            'core.config_entries' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'core.config_entries',
                'data'          => [
                    'entries' => [
                        new ConfigEntry(
                            domain: 'sun',
                            title: 'The Day Star',
                            data: [],
                            options: [],
                            disableNewEntities: false,
                            disablePolling: false,
                            source: 'import',
                            disabledBy: null
                        ),
                    ],
                ],
            ],
        ]);

        parent::emit($logger, $fc);
    }
}
