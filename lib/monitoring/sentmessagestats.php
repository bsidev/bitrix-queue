<?php

namespace Bsi\Queue\Monitoring;

use Symfony\Component\Messenger\Envelope;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class SentMessageStats
{
    /** @var Envelope */
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
