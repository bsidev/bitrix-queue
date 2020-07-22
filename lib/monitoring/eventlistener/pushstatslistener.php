<?php

namespace Bsi\Queue\Monitoring\EventListener;

use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Monitoring\MessageStatuses;
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
    /** @var AdapterInterface */
    private $adapter;
    /** @var string[] */
    private $busNames;

    public function __construct(AdapterInterface $adapter, array $busNames = [])
    {
        $this->adapter = $adapter;
        $this->busNames = $busNames;
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        if (!$this->isEnvelopeEnabled($event->getEnvelope())) {
            return;
        }

        $this->adapter->getStorage()->pushSentMessageStats($event->getEnvelope());
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        if (!$this->isEnvelopeEnabled($event->getEnvelope())) {
            return;
        }

        $this->adapter->getStorage()->pushConsumedMessageStats(
            $event->getEnvelope(),
            MessageStatuses::RECEIVED,
            $event->getReceiverName()
        );
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        if (!$this->isEnvelopeEnabled($event->getEnvelope())) {
            return;
        }

        $this->adapter->getStorage()->pushConsumedMessageStats(
            $event->getEnvelope(),
            MessageStatuses::HANDLED,
            $event->getReceiverName()
        );
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if (!$this->isEnvelopeEnabled($event->getEnvelope())) {
            return;
        }

        $this->adapter->getStorage()->pushConsumedMessageStats(
            $event->getEnvelope(),
            MessageStatuses::FAILED,
            $event->getReceiverName(),
            $event->getThrowable()
        );
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
