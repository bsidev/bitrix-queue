<?php

namespace Bsi\Queue\Monitoring\EventListener;

use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\SentMessageStats;
use Bsi\Queue\Monitoring\Storage\StorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class PushStatsListener implements EventSubscriberInterface
{
    /** @var StorageInterface */
    private $storage;
    /** @var string[] */
    private $busNames;

    public function __construct(StorageInterface $storage, array $busNames = [])
    {
        $this->storage = $storage;
        $this->busNames = $busNames;
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        if (!$this->isEnvelopeEnabled($event->getEnvelope())) {
            return;
        }

        $this->storage->pushSent(new SentMessageStats($event->getEnvelope()));
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        if (!$this->isEnvelopeEnabled($event->getEnvelope())) {
            return;
        }

        $this->storage->pushConsumed(new ConsumedMessageStats(
            $event->getEnvelope(),
            $event->getReceiverName(),
            ConsumedMessageStats::STATUS_RECEIVED
        ));
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        if (!$this->isEnvelopeEnabled($event->getEnvelope())) {
            return;
        }

        $this->storage->pushConsumed(new ConsumedMessageStats(
            $event->getEnvelope(),
            $event->getReceiverName(),
            ConsumedMessageStats::STATUS_HANDLED
        ));
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if (!$this->isEnvelopeEnabled($event->getEnvelope())) {
            return;
        }

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

    private function getBusesFromEnvelope(Envelope $envelope): array
    {
        $busNames = [];

        $stamps = $envelope->all(BusNameStamp::class);
        /** @var BusNameStamp $stamp */
        foreach ($stamps as $stamp) {
            $busNames[] = $stamp->getBusName();
        }

        return $busNames;
    }

    private function isEnvelopeEnabled(Envelope $envelope): bool
    {
        if (empty($this->busNames)) {
            return true;
        }

        $busNames = $this->getBusesFromEnvelope($envelope);

        return count(array_intersect($this->busNames, $busNames)) > 0;
    }
}
