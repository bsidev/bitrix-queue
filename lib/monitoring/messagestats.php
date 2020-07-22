<?php

namespace Bsi\Queue\Monitoring;

use Symfony\Component\Messenger\Envelope;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class MessageStats
{
    /** @var Envelope */
    private $envelope;
    /** @var string */
    private $status;
    /** @var string|null */
    private $transportName;
    /** @var string|null */
    private $error;
    /** @var \DateTimeInterface */
    private $sentAt;
    /** @var \DateTimeInterface|null */
    private $receivedAt;
    /** @var \DateTimeInterface|null */
    private $handledAt;
    /** @var \DateTimeInterface|null */
    private $failedAt;

    public function __construct(
        Envelope $envelope,
        string $status,
        ?string $transportName,
        ?string $error,
        \DateTimeInterface $sentAt,
        ?\DateTimeInterface $receivedAt,
        ?\DateTimeInterface $handledAt,
        ?\DateTimeInterface $failedAt
    ) {
        $this->envelope = $envelope;
        $this->status = $status;
        $this->transportName = $transportName;
        $this->error = $error;
        $this->sentAt = $sentAt;
        $this->receivedAt = $receivedAt;
        $this->handledAt = $handledAt;
        $this->failedAt = $failedAt;
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTransportName(): ?string
    {
        return $this->transportName;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getSentAt(): \DateTimeInterface
    {
        return $this->sentAt;
    }

    public function getReceivedAt(): ?\DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function getHandledAt(): ?\DateTimeInterface
    {
        return $this->handledAt;
    }

    public function getFailedAt(): ?\DateTimeInterface
    {
        return $this->failedAt;
    }
}
