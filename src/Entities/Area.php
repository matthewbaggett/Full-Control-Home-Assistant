<?php

declare(strict_types=1);

namespace FullControl\Entities;

use FullControl\Storage\AreaRegistry;
use FullControl\Storage\ConfigEntry;
use FullControl\Storage\DeviceRegistry;

class Area extends FullControlHomeAssistantEntity
{
    public function toAreaRegistry(): AreaRegistry
    {
        return new AreaRegistry(
            name: $this->name,
        );
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
