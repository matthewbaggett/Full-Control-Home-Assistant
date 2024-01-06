<?php

declare(strict_types=1);

namespace FullControl\Storage;

abstract class AbstractConfigEntry implements \JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return $this->__toArray();
    }

    public function __toArray(): array
    {
        $vars = get_object_vars($this);
        ksort($vars);

        return $vars;
    }
}
