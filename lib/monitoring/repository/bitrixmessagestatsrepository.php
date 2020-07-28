<?php

namespace Bsi\Queue\Monitoring\Repository;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bsi\Queue\Monitoring\Adapter\Bitrix\BitrixMessageStatTable;
use Bsi\Queue\Monitoring\MessageStats;
use Bsi\Queue\Monitoring\MessageStatsCollection;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixMessageStatsRepository implements MessageStatsRepositoryInterface
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    /**
     * {@inheritDoc}
     */
    public function countAll(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixMessageStatTable::getCount([
            '><SENT_AT' => $this->getDateRange($from, $to),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function countSent(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixMessageStatTable::getCount([
            '><SENT_AT' => $this->getDateRange($from, $to),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function countReceived(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixMessageStatTable::getCount([
            '><RECEIVED_AT' => $this->getDateRange($from, $to),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function countHandled(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixMessageStatTable::getCount([
            '><HANDLED_AT' => $this->getDateRange($from, $to),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function countFailed(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) BitrixMessageStatTable::getCount([
            '><FAILED_AT' => $this->getDateRange($from, $to),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getSentChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array
    {
        return $this->getDatasetByField('SENT_AT', $from->getTimestamp(), $to->getTimestamp(), $interval);
    }

    /**
     * {@inheritDoc}
     */
    public function getReceivedChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array
    {
        return $this->getDatasetByField('RECEIVED_AT', $from->getTimestamp(), $to->getTimestamp(), $interval);
    }

    /**
     * {@inheritDoc}
     */
    public function getHandledChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array
    {
        return $this->getDatasetByField('HANDLED_AT', $from->getTimestamp(), $to->getTimestamp(), $interval);
    }

    /**
     * {@inheritDoc}
     */
    public function getFailedChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array
    {
        return $this->getDatasetByField('FAILED_AT', $from->getTimestamp(), $to->getTimestamp(), $interval);
    }

    /**
     * {@inheritDoc}
     */
    public function getRecentList(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        int $limit,
        int $offset
    ): MessageStatsCollection {
        $collection = new MessageStatsCollection();

        $dbResult = BitrixMessageStatTable::getList([
            'select' => ['*'],
            'filter' => [
                [
                    'LOGIC' => 'OR',
                    ['><SENT_AT' => $this->getDateRange($from, $to)],
                    ['><RECEIVED_AT' => $this->getDateRange($from, $to)],
                    ['><HANDLED_AT' => $this->getDateRange($from, $to)],
                    ['><FAILED_AT' => $this->getDateRange($from, $to)],
                ],
            ],
            'order' => ['SENT_AT' => 'DESC', 'ID' => 'DESC'],
            'limit' => $limit,
            'offset' => $offset,
        ]);
        while ($row = $dbResult->fetch()) {
            $collection->add($this->createMessageStatsFromData($row));
        }

        return $collection;
    }

    private function getDateRange(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return [
            DateTime::createFromTimestamp($from->getTimestamp()),
            DateTime::createFromTimestamp($to->getTimestamp()),
        ];
    }

    private function getDatasetByField(string $field, int $fromTs, int $toTs, int $interval): array
    {
        $dataset = [];

        $dbResult = BitrixMessageStatTable::getList([
            'select' => ['TIMESTAMP', 'CNT'],
            'filter' => [
                "><{$field}" => [
                    DateTime::createFromTimestamp($fromTs),
                    DateTime::createFromTimestamp($toTs),
                ],
            ],
            'group' => ['TIMESTAMP'],
            'runtime' => [
                (new ExpressionField('TIMESTAMP', "UNIX_TIMESTAMP(%s) DIV {$interval} * {$interval}", [$field])),
                (new ExpressionField('CNT', 'COUNT(1)')),
            ],
        ]);

        while ($row = $dbResult->fetch()) {
            $dataset[] = [(int) $row['TIMESTAMP'], (int) $row['CNT']];
        }

        return $dataset;
    }

    private function createMessageStatsFromData(array $data): MessageStats
    {
        $envelope = $this->serializer->decode([
            'body' => $data['BODY'],
            'headers' => $data['HEADERS'],
        ]);

        return new MessageStats(
            $envelope,
            $data['STATUS'],
            $data['TRANSPORT_NAME'],
            $data['ERROR'],
            new \DateTime('@' . $data['SENT_AT']->getTimestamp()),
            $data['RECEIVED_AT'] instanceof Date ? new \DateTime('@' . $data['SENT_AT']->getTimestamp()) : null,
            $data['HANDLED_AT'] instanceof Date ? new \DateTime('@' . $data['HANDLED_AT']->getTimestamp()) : null,
            $data['FAILED_AT'] instanceof Date ? new \DateTime('@' . $data['FAILED_AT']->getTimestamp()) : null
        );
    }
}
