<?php

namespace Bsi\Queue\Event;

use Symfony\Component\Messenger\Envelope;

class SyncMessageHandledEvent
{
    private $envelope;

    public function __construct(Envelope $envelope)
    {
        $this->envelope = $envelope;
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }
}
