<?php

namespace Bsi\Queue\Console;

use Bsi\Queue\Queue;
use Bsi\Queue\Exception\LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BridgeCommand extends Command
{
    private Command $decorated;

    public function __construct(?string $name = null)
    {
        $container = Queue::getInstance()->getContainer();

        $command = $container->get($this->getDecoratedId());
        if (!$command instanceof Command) {
            throw new LogicException(sprintf(
                'The service "%s" must be an instance of %s.',
                $this->getDecoratedId(),
                Command::class
            ));
        }

        $this->decorated = $command;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName($this->getName() ?: $this->decorated->getName())
            ->setAliases($this->decorated->getAliases())
            ->setHelp($this->getHelp() ?: $this->decorated->getHelp())
            ->setDescription($this->getDescription() ?: $this->decorated->getDescription())
            ->setDefinition($this->decorated->getDefinition());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->decorated->execute($input, $output);
    }

    abstract protected function getDecoratedId(): string;
}
