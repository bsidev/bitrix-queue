<?php

namespace Bsi\Queue\Monitoring\Adapter\Bitrix;

use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Monitoring\Repository\BitrixMetricRepository;
use Bsi\Queue\Monitoring\Repository\MetricRepositoryInterface;
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

    public function getMetricRepository(): MetricRepositoryInterface
    {
        return new BitrixMetricRepository();
    }
}
