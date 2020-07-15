<?php

namespace Bsi\Queue\Monitoring\Repository;

use Bsi\Queue\Monitoring\ChartDataSet;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface ChartDataRepositoryInterface
{
    public function sentMessages(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): ChartDataSet;

    public function receivedMessages(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): ChartDataSet;

    public function handledMessages(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): ChartDataSet;

    public function failedMessages(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): ChartDataSet;
}
