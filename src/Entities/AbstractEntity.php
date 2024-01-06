<?php

declare(strict_types=1);

namespace FullControl\Entities;

use FullControl\FullControl;
use Monolog\Logger;

abstract class AbstractEntity
{
    public function emit(Logger $logger, FullControl $fc): void
    {
        $logger = $logger->withName(sprintf(
            '%s->%s',
            $logger->getName(),
            array_reverse(explode('\\', get_called_class()))[0]
        ));

        $logger->debug('emit');
    }
}
