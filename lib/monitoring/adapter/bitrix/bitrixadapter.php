<?php

namespace Bsi\Queue\Monitoring\Adapter\Bitrix;

use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Monitoring\Repository\BitrixMessageStatsRepository;
use Bsi\Queue\Monitoring\Repository\MessageStatsRepositoryInterface;
use Bsi\Queue\Monitoring\Storage\BitrixStorage;
use Bsi\Queue\Monitoring\Storage\StorageInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixAdapter implements AdapterInterface
{
    public function getStorage(): StorageInterface
    {
        return new BitrixStorage();
    }

    public function getMessageStatsRepository(): MessageStatsRepositoryInterface
    {
        return new BitrixMessageStatsRepository();
    }
}
