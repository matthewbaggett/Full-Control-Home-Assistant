<?php

declare(strict_types=1);

namespace FullControl\Entities;

use FullControl\Enums\MDI;
use FullControl\FullControl;
use FullControl\Storage\AbstractStorage;
use FullControl\Storage\ConfigEntry;
use FullControl\Storage\DeviceRegistry;
use FullControl\Storage\EntityRegistry;
use FullControl\Traits;
use Monolog\Logger;

abstract class FullControlHomeAssistantEntity extends AbstractStorage
{
    use Traits\SanitiseTrait;

    /**
     * @var array FullControlHomeAssistantEntity[]
     */
    protected array $entities;
    protected readonly string $domain;

    public function __construct(
        protected readonly ?string $name = null,
        protected readonly ?Area $area = null,
        protected readonly ?MDI $icon = null,
    ) {
        // Set domain to be just the unqualified class name snake_cased and after stripping non-alphanumeric characters
        $this->domain = strtolower(preg_replace('/[^a-z0-9]+/i', '_', array_reverse(explode('\\', get_called_class()))[0]));
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getPlatform(): string
    {
        return static::PLATFORM;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function getIcon(): ?MDI
    {
        return $this->icon;
    }

    public function emit(Logger $logger, FullControl $fc): void
    {
        $logger = $logger->withName(sprintf(
            '%s->%s',
            $logger->getName(),
            array_reverse(explode('\\', get_called_class()))[0]
        ));

        $logger->debug('emit');
    }

    abstract public function toConfigEntry(): ?ConfigEntry;

    abstract public function toDeviceRegistry(): ?DeviceRegistry;

    /**
     * @return EntityRegistry[]
     */
    abstract public function toEntityRegistry(): array;
}
