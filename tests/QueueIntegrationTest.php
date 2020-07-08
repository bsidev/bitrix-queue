<?php

namespace Bsi\Queue\Tests;

use Bsi\Queue\Queue;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Bsi\Queue\Tests\Fixtures\DummyMessageHandler;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @group time-sensitive
 * @group integration
 */
class QueueIntegrationTest extends AbstractTestCase
{
    public function testItRoutesToDefaultBus(): void
    {
        $this->getBitrixConfigurationMock([
            'buses' => [
                'command_bus',
                'query_bus',
            ],
            'default_bus' => 'command_bus',
        ]);
        $this->getBitrixEventMock();

        $queue = Queue::getInstance();
        $queue->addMessageHandler(DummyMessageHandler::class);
        $queue->boot();

        $envelope = $queue->dispatchMessage(new DummyMessage('Hello'));
        /** @var BusNameStamp $busNameStamp */
        $busNameStamp = $envelope->last(BusNameStamp::class);
        $this->assertInstanceOf(BusNameStamp::class, $busNameStamp);
        $this->assertSame('command_bus', $busNameStamp->getBusName());
    }

    public function testItRoutesToTheCorrectBus(): void
    {
        $this->getBitrixConfigurationMock([
            'buses' => [
                'command_bus',
                'query_bus',
            ],
            'default_bus' => 'command_bus',
        ]);
        $this->getBitrixEventMock();

        $queue = Queue::getInstance();
        $queue->addMessageHandler(DummyMessageHandler::class);
        $queue->boot();

        $envelope = $queue->dispatchMessage(new DummyMessage('Hello'), 'query_bus');

        /** @var BusNameStamp $busNameStamp */
        $busNameStamp = $envelope->last(BusNameStamp::class);
        $this->assertInstanceOf(BusNameStamp::class, $busNameStamp);
        $this->assertSame('query_bus', $busNameStamp->getBusName());
    }

    public function testItRoutesToTheCorrectTransport(): void
    {
        $this->getBitrixConfigurationMock([
            'buses' => [
                'command_bus',
                'query_bus',
            ],
            'default_bus' => 'command_bus',
            'transports' => [
                'sync' => 'sync://',
                'memory' => 'in-memory://',
            ],
            'routing' => [
                DummyMessage::class => 'memory',
            ],
        ]);
        $this->getBitrixEventMock();

        $queue = Queue::getInstance();
        $queue->addMessageHandler(DummyMessageHandler::class);
        $queue->boot();

        $envelope = $queue->dispatchMessage(new DummyMessage('Hello'));
        /** @var SentStamp $sentStamp */
        $sentStamp = $envelope->last(SentStamp::class);
        $this->assertSame('memory', $sentStamp->getSenderAlias());
    }
}
