<?php

declare(strict_types=1);

namespace Bsi\Queue\Tests\Fixtures\Monitoring;

use Bsi\Queue\Monitoring\MessageStatsCollection;
use Bsi\Queue\Monitoring\Repository\MessageStatsRepositoryInterface;

class DummyMessageStatsRepository implements MessageStatsRepositoryInterface
{
    public function countAll(\DateTimeInterface $from, \DateTimeInterface $to, string $search = ''): int
    {
        return 0;
    }

    public function countSent(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return 0;
    }

    public function countReceived(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return 0;
    }

    public function countHandled(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return 0;
    }

    public function countFailed(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return 0;
    }

    public function getSentChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array
    {
        return [];
    }

    public function getReceivedChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array
    {
        return [];
    }

    public function getHandledChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array
    {
        return [];
    }

    public function getFailedChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array
    {
        return [];
    }

    public function getRecentList(\DateTimeInterface $from, \DateTimeInterface $to, int $limit, int $offset, string $search = ''): MessageStatsCollection
    {
        return new MessageStatsCollection();
    }
}
