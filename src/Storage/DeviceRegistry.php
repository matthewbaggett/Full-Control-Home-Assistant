<?php

declare(strict_types=1);

namespace FullControl\Storage;

use FullControl\Entities\Area;
use FullControl\Enums\EntryType;

class DeviceRegistry extends AbstractStorage
{
    public function __construct(
        readonly protected ?Area $area,
        readonly protected array $configEntries,
        readonly protected ?string $configurationUrl,
        readonly protected array $connections,
        readonly protected ?string $disabledBy,
        readonly protected EntryType $entryType,
        readonly protected ?string $hwVersion,
        readonly protected ?string $manufacturer,
        readonly protected ?string $model,
        readonly protected ?string $serialNumber,
        readonly protected ?string $name,
        readonly protected ?string $swVersion,
        readonly protected ?string $viaDeviceId, // @todo Make this take a Device struct
    ) {
    }

    public function __toArray(): array
    {
        $device = array_merge(
            parent::__toArray(),
            [
                'config_entries' => array_map(fn (ConfigEntry $configEntry) => $configEntry->getEntryId(), $this->configEntries),
                'id'             => $this->getEntryId(),
                'name_by_user'   => null,
                'identifiers'    => $this->getIdentifiers(),
            ]
        );
        ksort($device);

        return $device;
    }

    private function getIdentifiers(): array
    {
        return [
            [
                $this->getUniqueId(), $this->getEntryId(),
            ],
        ];
    }
}
