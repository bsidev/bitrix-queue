<?php

namespace Bsi\Queue\Monitoring\Adapter;

use Bsi\Queue\Monitoring\Repository\ChartDataRepositoryInterface;
use Bsi\Queue\Monitoring\Repository\MetricRepositoryInterface;
use Bsi\Queue\Monitoring\Storage\StorageInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface AdapterInterface
{
    public function getStorage(): StorageInterface;

    public function getMetricRepository(): MetricRepositoryInterface;

    public function getChartDataRepository(): ChartDataRepositoryInterface;
}
