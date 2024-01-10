<?php

declare(strict_types=1);

namespace FullControl\Traits;

trait SanitiseTrait
{
    // Takes a string and converts it to snake case alphanumeric characters
    public function snakeCase(string $string): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '_', $string));
    }

    // Takes a string and converts it to camel case alphanumeric characters
    public function camelCase(string $string): string
    {
        return lcfirst(preg_replace('/[^a-z0-9]+/i', '', ucwords($string, '_')));
    }

    // Takes a string and converts it to pascal case alphanumeric characters
    public function pascalCase(string $string): string
    {
        return preg_replace('/[^a-z0-9]+/i', '', ucwords($string, '_'));
    }
}
