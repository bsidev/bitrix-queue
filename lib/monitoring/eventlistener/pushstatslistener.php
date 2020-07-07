<?php

namespace Bsi\Queue\Monitoring\EventListener;

use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\SentMessageStats;
use Bsi\Queue\Monitoring\Storage\StorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class PushStatsListener implements EventSubscriberInterface
{
    /** @var StorageInterface */
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        $this->storage->pushSent(new SentMessageStats($event->getEnvelope()));
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $this->storage->pushConsumed(new ConsumedMessageStats(
            $event->getEnvelope(),
            $event->getReceiverName(),
            ConsumedMessageStats::STATUS_RECEIVED
        ));
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $this->storage->pushConsumed(new ConsumedMessageStats(
            $event->getEnvelope(),
            $event->getReceiverName(),
            ConsumedMessageStats::STATUS_HANDLED
        ));
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $this->storage->pushConsumed(new ConsumedMessageStats(
            $event->getEnvelope(),
            $event->getReceiverName(),
            ConsumedMessageStats::STATUS_FAILED
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SendMessageToTransportsEvent::class => ['onMessageSent', 99999],
            WorkerMessageReceivedEvent::class => ['onMessageReceived', 99999],
            WorkerMessageHandledEvent::class => ['onMessageHandled', 99999],
            WorkerMessageFailedEvent::class => ['onMessageFailed', 99999],
        ];
    }
}
