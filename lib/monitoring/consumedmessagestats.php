<?php

namespace Bsi\Queue\Monitoring;

use Symfony\Component\Messenger\Envelope;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class ConsumedMessageStats
{
    public const STATUS_RECEIVED = 'received';
    public const STATUS_HANDLED = 'handled';
    public const STATUS_FAILED = 'failed';

    /** @var Envelope */
    private $envelope;
    /** @var string */
    private $transport;
    /** @var string */
    private $status;

    public function __construct(Envelope $envelope, string $transport, string $status)
    {
        $this->envelope = $envelope;
        $this->transport = $transport;
        $this->status = $status;
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
