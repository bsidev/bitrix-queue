<?php

use Bsi\Queue\Console\ConsoleCommand;

return [
    'controllers' => [
        'value' => [
            'namespaces' => [
                'Bsi\\Queue\\Monitoring\\Controller' => 'api',
            ],
        ],
        'readonly' => true,
    ],
    'console' => [
        'value' => [
            'commands' => [
                ConsoleCommand::class,
            ],
        ],
        'readonly' => true,
    ],
];
