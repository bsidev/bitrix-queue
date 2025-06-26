<?php

namespace Bsi\Queue\Console;

use Bsi\Queue\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;

class ConsoleCommand extends Command
{
    private ConsumeMessagesCommand $consumeCommand;

    public function __construct(?string $name = null)
    {
        $container = Queue::getInstance()->getContainer();
        $this->consumeCommand = $container->get('console.command.messenger_consume_messages');

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('bsi.queue:consume')
            ->setAliases($this->consumeCommand->getAliases())
            ->setHelp($this->consumeCommand->getHelp())
            ->setDescription($this->consumeCommand->getDescription())
            ->setDefinition($this->consumeCommand->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->consumeCommand->execute($input, $output);
    }
}
