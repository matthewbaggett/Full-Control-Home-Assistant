<?php

declare(strict_types=1);

namespace FullControl\Storage;

class ConfigEntry extends AbstractConfigEntry
{
    public function __construct(
        readonly protected string $domain,
        readonly protected string $title,
        readonly protected array $data,
        readonly protected array $options,
        readonly protected bool $disableNewEntities,
        readonly protected bool $disablePolling,
        readonly protected string $source,
        readonly protected ?string $disabledBy, // @todo replace with enum
    ) {
    }

    public function __toArray(): array
    {
        return [
            'entry_id'  => $this->getEntryId(),
            'unique_id' => $this->getUniqueId(),
        ] + parent::__toArray();
    }

    public function getUniqueId(): string
    {
        return hash('tiger128,4', json_encode(parent::__toArray()));
    }

    public function getEntryId(): string
    {
        return hash('tiger128,3', json_encode(parent::__toArray()));
    }
}
