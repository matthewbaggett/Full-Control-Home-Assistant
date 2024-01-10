<?php

declare(strict_types=1);

namespace FullControl\Entities;

use FullControl\Enums\MDI;
use FullControl\Storage\ConfigEntry;
use FullControl\Storage\DeviceRegistry;
use FullControl\Storage\EntityRegistry;
use FullControl\Storage\PersonRegistry;

class Person extends FullControlHomeAssistantEntity
{
    protected const PLATFORM = 'person';

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
        return [
            new EntityRegistry(
                fchae: $this,
                uniqueId: $this->pascalCase($this->getName()),
                originalName: $this->getName(),
                name: null,
                originalIcon: MDI::Person,
            ),
        ];
    }

    public function toPersonEntity(): PersonRegistry
    {
        return new PersonRegistry($this);
    }
}
