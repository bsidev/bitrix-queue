<?php

namespace Bsi\Queue\Monitoring\Repository;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;
use Bsi\Queue\Monitoring\ChartDataSet;
use Bsi\Queue\Monitoring\Entity\BitrixStatTable;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixChartDataRepository implements ChartDataRepositoryInterface
{
    public function sentMessages(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): ChartDataSet
    {
        return $this->getDatasetByField('CREATED_AT', $from->getTimestamp(), $to->getTimestamp(), $interval);
    }

    public function receivedMessages(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): ChartDataSet
    {
        return $this->getDatasetByField('RECEIVED_AT', $from->getTimestamp(), $to->getTimestamp(), $interval);
    }

    public function handledMessages(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): ChartDataSet
    {
        return $this->getDatasetByField('HANDLED_AT', $from->getTimestamp(), $to->getTimestamp(), $interval);
    }

    public function failedMessages(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): ChartDataSet
    {
        return $this->getDatasetByField('FAILED_AT', $from->getTimestamp(), $to->getTimestamp(), $interval);
    }

    private function getDatasetByField(string $field, int $fromTs, int $toTs, int $interval): ChartDataSet
    {
        $dataset = new ChartDataSet();

        $dbResult = BitrixStatTable::getList([
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
            $dataset->addValue((int) $row['TIMESTAMP'], (int) $row['CNT']);
        }

        return $dataset;
    }
}
