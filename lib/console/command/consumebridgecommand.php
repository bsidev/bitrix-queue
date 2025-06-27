<?php

declare(strict_types=1);

namespace Bsi\Queue\Console\Command;

use Bsi\Queue\Console\BridgeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'bsi.queue:consume')]
class ConsumeBridgeCommand extends BridgeCommand
{
    protected function getDecoratedId(): string
    {
        return 'console.command.messenger_consume_messages';
    }
}
