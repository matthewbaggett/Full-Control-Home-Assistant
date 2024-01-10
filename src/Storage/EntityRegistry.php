<?php

declare(strict_types=1);

namespace FullControl\Storage;

use FullControl\Entities\Area;
use FullControl\Entities\FullControlHomeAssistantEntity as FCHAE;
use FullControl\Enums\MDI;

class EntityRegistry extends AbstractStorage
{
    public function __construct(
        readonly protected FCHAE $fchae,
        readonly protected string $uniqueId,
        readonly protected string $originalName,
        readonly protected null | string $name,
        readonly protected array | null $aliases = [],
        readonly protected mixed $capabilities = null,
        readonly protected null | string $deviceClass = null,
        readonly protected null | string $disabledBy = null,
        readonly protected null | string $entityCategory = null,
        readonly protected null | string $hiddenBy = null,
        readonly protected bool $hasEntityName = true,
        readonly protected null | array $options = ['conversation' => ['should_expose' => false]],
        readonly protected null | string $originalDeviceClass = null,
        readonly protected null | MDI $originalIcon = null,
        readonly protected int $supportedFeatures = 0,
        readonly protected null | string $unitOfMeasurement = null,
    ) {
    }

    public function __toArray(): array
    {
        \Kint::dump($this);
        $device = array_merge(
            parent::__toArray(),
            [
                'entity_id'      => sprintf(
                    'sensor.%s_%s',
                    $this->getDomain(),
                    $this->uniqueId,
                ),
                'area_id'         => $this->getArea()?->getEntryId(),
                'config_entry_id' => $this->getConfigEntry()?->getEntryId(),
                'id'              => $this->getEntryId(),
                'translation_key' => $this->getUniqueId(),
                'platform'        => $this->getPlatform(),
                'icon'            => $this->getIcon(),
                'device_id'       => $this->getDevice()?->getEntryId(),

                // 'name_by_user'   => null,
                // 'identifiers'    => $this->getIdentifiers(),
            ]
        );
        ksort($device);

        return $device;
    }

    public function getDomain(): string
    {
        return $this->fchae->getDomain();
    }

    public function getUniqueId(): string
    {
        if ($this->getConfigEntry()) {
            return sprintf('%s-%s', $this->getConfigEntry()->getEntryId(), $this->uniqueId);
        }

        return $this->uniqueId;
    }

    public function getArea(): ?Area
    {
        return $this->fchae->getArea();
    }

    public function getConfigEntry(): ?ConfigEntry
    {
        return $this->fchae->toConfigEntry();
    }

    public function getDevice(): ?DeviceRegistry
    {
        return $this->fchae->toDeviceRegistry();
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getPlatform(): string
    {
        return $this->fchae->getPlatform();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function getCapabilities(): mixed
    {
        return $this->capabilities;
    }

    public function getDeviceClass(): ?string
    {
        return $this->deviceClass;
    }

    public function getDisabledBy(): ?string
    {
        return $this->disabledBy;
    }

    public function getEntityCategory(): ?string
    {
        return $this->entityCategory;
    }

    public function getHiddenBy(): ?string
    {
        return $this->hiddenBy;
    }

    public function getIcon(): ?MDI
    {
        return $this->fchae->getIcon() ?? null;
    }

    public function isHasEntityName(): bool
    {
        return $this->hasEntityName;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function getOriginalDeviceClass(): ?string
    {
        return $this->originalDeviceClass;
    }

    public function getOriginalIcon(): ?MDI
    {
        return $this->originalIcon;
    }

    public function getSupportedFeatures(): int
    {
        return $this->supportedFeatures;
    }

    public function getUnitOfMeasurement(): ?string
    {
        return $this->unitOfMeasurement;
    }
}
