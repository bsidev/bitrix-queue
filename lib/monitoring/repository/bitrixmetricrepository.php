<?php

namespace Bsi\Queue\Monitoring\Repository;

use Bitrix\Main\Type\DateTime;
use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\Entity\BitrixStatTable;
use Bsi\Queue\Monitoring\SentMessageStats;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixMetricRepository implements MetricRepositoryInterface
{
    public function countSentMessages(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixStatTable::getCount([
            'STATUS' => SentMessageStats::STATUS,
            '><CREATED_AT' => $this->getDateRange($from, $to),
        ]);
    }

    public function countReceivedMessages(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixStatTable::getCount([
            'STATUS' => ConsumedMessageStats::STATUS_RECEIVED,
            '><CREATED_AT' => $this->getDateRange($from, $to),
        ]);
    }

    public function countHandledMessages(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixStatTable::getCount([
            'STATUS' => ConsumedMessageStats::STATUS_HANDLED,
            '><CREATED_AT' => $this->getDateRange($from, $to),
        ]);
    }

    public function countFailedMessages(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixStatTable::getCount([
            'STATUS' => ConsumedMessageStats::STATUS_FAILED,
            '><CREATED_AT' => $this->getDateRange($from, $to),
        ]);
    }

    private function getDateRange(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return [
            DateTime::createFromTimestamp($from->getTimestamp()),
            DateTime::createFromTimestamp($to->getTimestamp()),
        ];
    }
}
