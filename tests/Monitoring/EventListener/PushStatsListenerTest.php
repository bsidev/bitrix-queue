<?php

namespace Bsi\Queue\Tests\Monitoring\EventListener;

use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\EventListener\PushStatsListener;
use Bsi\Queue\Monitoring\SentMessageStats;
use Bsi\Queue\Tests\AbstractTestCase;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

class PushStatsListenerTest extends AbstractTestCase
{
    public function testPushStatsOnMessageSent(): void
    {
        $envelope = new Envelope(new \stdClass());

        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter->shouldReceive('getStorage->pushSentMessageStats')
            ->andReturnUsing(function (SentMessageStats $stats) use ($envelope) {
                $this->assertEquals($envelope, $stats->getEnvelope());
            });

        $listener = new PushStatsListener($adapter);
        $event = new SendMessageToTransportsEvent($envelope);
        $listener->onMessageSent($event);
    }

    public function testPushStatsOnMessageReceived(): void
    {
        $envelope = new Envelope(new \stdClass());
        $receiverName = 'dummy_receiver';

        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter->shouldReceive('getStorage->pushConsumedMessageStats')
            ->andReturnUsing(function (ConsumedMessageStats $stats) use ($envelope, $receiverName) {
                $this->assertEquals($envelope, $stats->getEnvelope());
                $this->assertSame($receiverName, $stats->getTransport());
                $this->assertSame(ConsumedMessageStats::STATUS_RECEIVED, $stats->getStatus());
            });

        $listener = new PushStatsListener($adapter);
        $event = new WorkerMessageReceivedEvent($envelope, $receiverName);
        $listener->onMessageReceived($event);
    }

    public function testPushStatsOnMessageHandled(): void
    {
        $envelope = new Envelope(new \stdClass());
        $receiverName = 'dummy_receiver';

        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter->shouldReceive('getStorage->pushConsumedMessageStats')
            ->andReturnUsing(function (ConsumedMessageStats $stats) use ($envelope, $receiverName) {
                $this->assertEquals($envelope, $stats->getEnvelope());
                $this->assertSame($receiverName, $stats->getTransport());
                $this->assertSame(ConsumedMessageStats::STATUS_HANDLED, $stats->getStatus());
            });

        $listener = new PushStatsListener($adapter);
        $event = new WorkerMessageHandledEvent($envelope, $receiverName);
        $listener->onMessageHandled($event);
    }

    public function testPushStatsOnMessageFailed(): void
    {
        $envelope = new Envelope(new \stdClass());
        $receiverName = 'dummy_receiver';

        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter->shouldReceive('getStorage->pushConsumedMessageStats')
            ->andReturnUsing(function (ConsumedMessageStats $stats) use ($envelope, $receiverName) {
                $this->assertEquals($envelope, $stats->getEnvelope());
                $this->assertSame($receiverName, $stats->getTransport());
                $this->assertSame(ConsumedMessageStats::STATUS_FAILED, $stats->getStatus());
            });

        $listener = new PushStatsListener($adapter);
        $event = new WorkerMessageFailedEvent($envelope, $receiverName, new \Exception());
        $listener->onMessageFailed($event);
    }
}
