<?php

namespace Bsi\Queue\Event;

use Symfony\Component\Messenger\Envelope;

class SyncMessageFailedEvent
{
    private $envelope;
    private $throwable;

    public function __construct(Envelope $envelope, \Throwable $throwable)
    {
        $this->envelope = $envelope;
        $this->throwable = $throwable;
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
