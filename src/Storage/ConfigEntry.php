<?php

declare(strict_types=1);

namespace FullControl\Storage;

class ConfigEntry extends AbstractConfigEntry
{
    protected Version $version;

    public function __construct(
        readonly protected string $domain,
        readonly protected ?string $name,
        readonly protected ?array $data,
        readonly protected ?array $options,
        readonly protected ?bool $prefDisableNewEntities,
        readonly protected ?bool $prefDisablePolling,
        readonly protected ?string $source,
        readonly protected ?string $disabledBy, // @todo replace with enum
    ) {
        $this->version = new Version(1, 1);
    }

    public function __toArray(): array
    {
        $config = [
            'entry_id'      => $this->getEntryId(),
            'unique_id'     => null, // $this->getUniqueId(),
            'version'       => $this->version->version,
            'minor_version' => $this->version->minorVersion,
            'title'         => $this->name,
        ] + parent::__toArray();

        unset($config['name']);

        return $config;
    }
}
