<?php

declare(strict_types=1);

namespace FullControl\Storage;

use FullControl\Entities\Person;

class PersonRegistry extends AbstractStorage
{
    public function __construct(
        readonly protected Person $person,
    ) {
    }

    public function __toArray(): array
    {
        return [
            'id'              => $this->person->getUniqueId(),
            'name'            => $this->person->getName(),
            'user_id'         => $this->person->getEntryId(),
            'device_trackers' => [],
        ];
    }
}
