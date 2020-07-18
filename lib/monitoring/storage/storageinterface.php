<?php

namespace Bsi\Queue\Monitoring\Storage;

use Symfony\Component\Messenger\Envelope;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface StorageInterface
{
    /**
     * Pushes a sent message stats.
     *
     * @param Envelope $envelope
     */
    public function pushSentMessageStats(Envelope $envelope): void;

    /**
     * Pushes a consumed message stats.
     *
     * @param Envelope $envelope
     * @param string $status
     * @param string $transportName
     * @param \Throwable|null $error
     */
    public function pushConsumedMessageStats(
        Envelope $envelope,
        string $status,
        string $transportName,
        \Throwable $error = null
    ): void;
}
