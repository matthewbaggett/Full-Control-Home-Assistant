<?php

declare(strict_types=1);

namespace FullControl\Storage;

class AreaRegistry extends AbstractStorage
{
    public function __construct(
        readonly protected ?string $name,
        readonly protected ?string $picture = null,
        readonly protected array $aliases = [],
    ) {
    }

    public function __toArray(): array
    {
        return parent::__toArray() + [
            'id' => $this->getUniqueId(),
        ];
    }
}
