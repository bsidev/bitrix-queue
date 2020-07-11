<?php

namespace Bsi\Queue\Monitoring\Repository;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface MetricRepositoryInterface
{
    public function countSentMessages(\DateTimeInterface $from, \DateTimeInterface $to): int;

    public function countReceivedMessages(\DateTimeInterface $from, \DateTimeInterface $to): int;

    public function countHandledMessages(\DateTimeInterface $from, \DateTimeInterface $to): int;

    public function countFailedMessages(\DateTimeInterface $from, \DateTimeInterface $to): int;
}
