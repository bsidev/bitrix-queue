<?php

declare(strict_types=1);

namespace Bsi\Queue\Tests\Fixtures\Monitoring;

use Symfony\Component\Messenger\Envelope;
use Bsi\Queue\Monitoring\Storage\StorageInterface;

class DummyStorage implements StorageInterface
{
    public function pushSentMessageStats(Envelope $envelope): void
    {
    }

    public function pushConsumedMessageStats(Envelope $envelope, string $status, string $transportName, \Throwable $error = null): void
    {
    }

    public function cleanUpStats(int $lifetimeInDays): void
    {
    }
}
