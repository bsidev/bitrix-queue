<?php

use Bsi\Queue\Console\Command\DebugBridgeCommand;
use Bsi\Queue\Console\Command\ConsumeBridgeCommand;
use Bsi\Queue\Console\Command\StopWorkersBridgeCommand;
use Bsi\Queue\Console\Command\FailedMessagesShowBridgeCommand;
use Bsi\Queue\Console\Command\FailedMessagesRetryBridgeCommand;
use Bsi\Queue\Console\Command\FailedMessagesRemoveBridgeCommand;

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
                ConsumeBridgeCommand::class,
                DebugBridgeCommand::class,
                FailedMessagesRetryBridgeCommand::class,
                FailedMessagesShowBridgeCommand::class,
                FailedMessagesRemoveBridgeCommand::class,
                StopWorkersBridgeCommand::class,
            ],
        ],
        'readonly' => true,
    ],
];
