<?php

declare(strict_types=1);

namespace FullControl\Storage;

class Version
{
    public function __construct(public int $version, public int $minorVersion)
    {
    }
}
