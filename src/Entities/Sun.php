<?php

declare(strict_types=1);

namespace FullControl\Entities;

use FullControl\Enums\EntryType;
use FullControl\Enums\MDI;
use FullControl\Storage\ConfigEntry;
use FullControl\Storage\DeviceRegistry;
use FullControl\Storage\EntityRegistry;

class Sun extends FullControlHomeAssistantEntity
{
    protected const PLATFORM = 'sun';
    protected const ICON     = MDI::SunClock;
    protected $manufacturer  = 'Full Control';
    protected $model         = 'Sun';
    protected $swVersion     = '1.0.0';
    protected $hwVersion;

    public function getIcon(): MDI
    {
        return $this->icon ?? self::ICON;
    }

    public function toConfigEntry(): ConfigEntry
    {
        return new ConfigEntry(
            domain: $this->domain,
            name: $this->name,
            data: [],
            options: [],
            prefDisableNewEntities: false,
            prefDisablePolling: false,
            source: 'import',
            disabledBy: null
        );
    }

    public function toDeviceRegistry(): DeviceRegistry
    {
        return new DeviceRegistry(
            area: $this->area,
            configEntries: [$this->toConfigEntry()],
            configurationUrl: null,
            connections: [],
            disabledBy: null,
            entryType: EntryType::Service,
            hwVersion: $this->hwVersion,
            manufacturer: $this->manufacturer,
            model: $this->model,
            serialNumber: null,
            name: $this->name,
            swVersion: $this->swVersion,
            viaDeviceId: null,
        );
    }

    /**
     * @return EntityRegistry[]
     */
    public function toEntityRegistry(): array
    {
        return [
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'next_dawn',
                originalName: 'Next Dawn',
                name: null,
                aliases: [],
                capabilities: null,
                deviceClass: null,
                disabledBy: null,
                entityCategory: 'diagnostic',
                hiddenBy: null,
                hasEntityName: true,
                options: [
                    'conversation' => [
                        'should_expose' => false,
                    ],
                ],
                originalDeviceClass: 'timestamp',
                originalIcon: MDI::SunClock,
                supportedFeatures: 0,
                unitOfMeasurement: null,
            ),
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'next_dusk',
                originalName: 'Next Dusk',
                name: null,
                entityCategory: 'diagnostic',
                originalDeviceClass: 'timestamp',
                originalIcon: MDI::SunClock,
            ),
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'next_midnight',
                originalName: 'Next Midnight',
                name: null,
                entityCategory: 'diagnostic',
                originalDeviceClass: 'timestamp',
                originalIcon: MDI::SunClock,
            ),
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'next_noon',
                originalName: 'Next Noon',
                name: null,
                entityCategory: 'diagnostic',
                originalDeviceClass: 'timestamp',
                originalIcon: MDI::SunClock,
            ),
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'next_rising',
                originalName: 'Next Rising',
                name: null,
                entityCategory: 'diagnostic',
                originalDeviceClass: 'timestamp',
                originalIcon: MDI::SunClock,
            ),
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'next_setting',
                originalName: 'Next Setting',
                name: null,
                entityCategory: 'diagnostic',
                originalDeviceClass: 'timestamp',
                originalIcon: MDI::SunClock,
            ),
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'solar_elevation',
                originalName: 'Elevation',
                name: null,
                entityCategory: 'diagnostic', // @todo Enum
                originalDeviceClass: 'elevation',
                originalIcon: MDI::ThemeLightDark,
            ),
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'solar_azimuth',
                originalName: 'Azimuth',
                name: null,
                entityCategory: 'diagnostic', // @todo Enum
                originalDeviceClass: 'azimuth',
                originalIcon: MDI::ThemeLightDark,
            ),
            new EntityRegistry(
                fchae: $this,
                uniqueId: 'solar_rising',
                originalName: 'Solar rising',
                name: null,
                entityCategory: 'diagnostic',
                originalIcon: MDI::SunClock,
            ),
        ];
    }
}
