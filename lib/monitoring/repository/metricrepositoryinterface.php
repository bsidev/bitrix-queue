<?php

namespace Bsi\Queue\Monitoring\Repository;

interface MetricRepositoryInterface
{
    public function countSentMessages(\DateTimeInterface $from, \DateTimeInterface $to): int;

    public function countReceivedMessages(\DateTimeInterface $from, \DateTimeInterface $to): int;

    public function countHandledMessages(\DateTimeInterface $from, \DateTimeInterface $to): int;

    public function countFailedMessages(\DateTimeInterface $from, \DateTimeInterface $to): int;
}
