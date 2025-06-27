<?php

declare(strict_types=1);

namespace Bsi\Queue\Console\Command;

use Bsi\Queue\Console\BridgeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'bsi.queue:failed:show')]
class FailedMessagesShowBridgeCommand extends BridgeCommand
{
    protected function getDecoratedId(): string
    {
        return 'console.command.messenger_failed_messages_show';
    }
}
