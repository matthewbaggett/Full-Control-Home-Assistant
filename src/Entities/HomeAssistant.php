<?php

declare(strict_types=1);

namespace FullControl\Entities;

use FullControl\FullControl;
use FullControl\Storage\ConfigEntry;
use FullControl\Storage\DeviceRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Tag\TaggedValue;

class HomeAssistant extends FullControlHomeAssistantEntity
{
    /**
     * @var array Area[]
     */
    protected array $areas;

    public function __construct(
        protected LoggerInterface $logger,
        ?string $name = null,
        ?Area $area = null,
        ?string $icon = null,
        readonly protected ?string $latitude = null,
        readonly protected ?string $longitude = null,
        readonly protected ?string $timezone = null,
        readonly protected ?string $currency = null,
        readonly protected ?string $country = null,
        readonly protected ?string $language = 'en',
    ) {
        parent::__construct(
            name: $name,
            area: $area,
            icon: $icon
        );

        // Make a default outside area
        $this->areas = [
            'outside' => $outside = (new Area(name: 'Outside')),
        ];

        // Make a default sun entity
        $this->entities = [
            'sun' => $sun = (new Sun(area: $outside)),
        ];
    }

    public function addAreas(Area ...$area): self
    {
        array_walk($area, function (Area $area): void {
            $this->areas[$area->getName()] = $area;
            $this->logger->info("Added area {$area->getName()}");
        });
        ksort($this->areas);

        return $this;
    }

    public function addEntities(FullControlHomeAssistantEntity ...$entity): self
    {
        array_walk($entity, function (FullControlHomeAssistantEntity $entity): void {
            $this->entities[$entity->getName()] = $entity;
            $this->logger->info("Added entity {$entity->getName()}");
        });
        ksort($this->entities);

        return $this;
    }

    public function emit(LoggerInterface $logger, FullControl $fc): void
    {
        $logger->info('entered emit');
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
                'data'          => [
                    'areas' => array_map(
                        fn (Area $area) => $area->toAreaRegistry(),
                        array_values($this->areas)
                    ),
                ],
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
                    // Call ->toConfigEntry() on each entity
                    'entries' => array_values(array_filter(array_map(
                        fn (FullControlHomeAssistantEntity $entity) => $entity->toConfigEntry(),
                        array_values($this->entities)
                    ))),
                ],
            ],

            // Device Registry
            'core.device_registry' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'core.device_registry',
                'data'          => [
                    // Call ->toDeviceRegistry() on each entity
                    'devices' => array_values(array_filter(array_map(
                        fn (FullControlHomeAssistantEntity $entity) => $entity->toDeviceRegistry(),
                        array_values($this->entities)
                    ))),
                    'deleted_devices' => [],
                ],
            ],

            // Entity Registry
            'core.entity_registry' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'core.entity_registry',
                'data'          => [
                    // Call ->toEntityRegistry() on each entity
                    'entities' => array_merge(...array_map(
                        fn (FullControlHomeAssistantEntity $entity) => $entity->toEntityRegistry(),
                        array_values($this->entities)
                    )),
                    'deleted_entities' => [],
                ],
            ],

            // Restore State
            'core.restore_state' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'core.restore_state',
                'data'          => [
                ],
            ],

            // ???
            'core.uuid' => null,

            // Pretty this is for yelling at computers
            'homeassistant.exposed_entities' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'homeassistant.exposed_entities',
                'data'          => [
                    'assistants'       => [],
                    'exposed_entities' => [],
                ],
            ],

            // http
            'http' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'http',
                'data'          => [
                    'ip_ban_enabled'           => true,
                    'login_attempts_threshold' => 5,
                    'server_port'              => 8123,
                    'ssl_profile'              => 'modern',
                    'cors_allowed_origins'     => [
                        'https://cast.home-assistant.io',
                    ],
                    'use_x_frame_options' => true,
                ],
            ],

            // http.auth
            'http.auth' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'http.auth',
                'data'          => [],
            ],

            // Onboarding
            'onboarding' => null,

            // Person
            'person' => [
                'version'       => 2,
                'minor_version' => 1,
                'key'           => 'person',
                'data'          => [
                    // 'storage_version' => 1,
                    'items'           => array_values(array_map(
                        fn (Person $person) => $person->toPersonEntity(),
                        array_filter($this->entities, fn (FullControlHomeAssistantEntity $entity) => $entity instanceof Person)
                    )),
                ],
            ],

            'repairs.issue_registry' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'repairs.issue_registry',
                'data'          => [
                    'issues' => [],
                ],
            ],

            'trace.saved_traces' => [
                'version'       => 1,
                'minor_version' => 1,
                'key'           => 'trace.saved_traces',
                'data'          => [],
            ],
        ]);

        parent::emit($logger, $fc);
    }

    public function toConfigEntry(): ?ConfigEntry
    {
        return null;
    }

    public function toDeviceRegistry(): ?DeviceRegistry
    {
        return null;
    }

    public function toEntityRegistry(): array
    {
        return [];
    }
}
