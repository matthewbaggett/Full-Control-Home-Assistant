<?php

declare(strict_types=1);

namespace FullControl;

use Garden\Cli\Cli;

class FullControl
{
    public function run(): void
    {
        $cli = new Cli();
        $cli->description('Start the Full Control Home Assistant application')
            ->opt(
                name: 'config:c',
                description: 'Specify configuration files to load, can be stated multiple times',
                required: false,
                type: 'string[]',
            )
        ;

        // Parse cli commands
        global $argv;
        $args = $cli->parse($argv, true);

        if ($args->hasOpt('config')) {
            \Kint::dump($args->getOpt('config'));

            exit(0);
        }
    }
}
