<?php

declare(strict_types=1);

namespace Bsi\Queue\Tests\Fixtures\Monitoring;

use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Monitoring\Storage\StorageInterface;
use Bsi\Queue\Monitoring\Repository\MessageStatsRepositoryInterface;

class DummyAdapter implements AdapterInterface
{
    public function getStorage(): StorageInterface
    {
        return new DummyStorage();
    }

    public function getMessageStatsRepository(): MessageStatsRepositoryInterface
    {
        return new DummyMessageStatsRepository();
    }
}
