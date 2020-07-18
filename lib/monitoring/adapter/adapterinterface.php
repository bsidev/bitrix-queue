<?php

namespace Bsi\Queue\Monitoring\Adapter;

use Bsi\Queue\Monitoring\Repository\MessageStatsRepositoryInterface;
use Bsi\Queue\Monitoring\Storage\StorageInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface AdapterInterface
{
    public function getStorage(): StorageInterface;

    public function getMessageStatsRepository(): MessageStatsRepositoryInterface;
}
