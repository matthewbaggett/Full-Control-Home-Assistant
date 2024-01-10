<?php

declare(strict_types=1);

namespace FullControl\Storage;

abstract class AbstractStorage implements \JsonSerializable
{
    protected readonly ?string $name;

    protected function __toShallowArray(): array
    {
        $reflectedClass = new \ReflectionClass($this);

        $properties     = $reflectedClass->getProperties();

        $vars = [];
        foreach ($properties as $property) {
            $vars[$property->getName()] = $property->isInitialized($this) ? $property->getValue($this) : null;
        }

        return $vars;
    }

    public function __toArray(): array
    {
        // Use reflection to get all properties of this class
        $reflectedClass = new \ReflectionClass($this);
        foreach ($reflectedClass->getProperties() as $prop) {
            $propValue = $prop->isInitialized($this) ? $prop->getValue($this) : null;

            if ($prop->getType() !== null && class_exists($prop->getType()->getName())
                                          && is_subclass_of($prop->getType()->getName(), AbstractStorage::class)) {
                $vars[$prop->getName() . 'Id'] = $propValue?->getUniqueId();
            } else {
                $vars[$prop->getName()] = $propValue;
            }
        }

        // Sort the array by key
        ksort($vars);

        \Kint::dump($vars);
        // Rename all keys to be snake_case
        return array_combine(
            array_map(
                fn ($key) => strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key)),
                array_keys($vars)
            ),
            array_values($vars)
        );
    }

    public function getUniqueId(): string
    {
        // return the name but snake_case and stripped of non-alphanumeric characters
        return strtolower(preg_replace(
            '/[^a-z0-9]+/i',
            '_',
            $this->name ?? $this->getEntryId()
        ));
    }

    public function getEntryId(): string
    {
        return hash('tiger128,3', json_encode($this->__toShallowArray()));
    }

    public function jsonSerialize(): mixed
    {
        return $this->__toArray();
    }
}
