<?php

namespace Bsi\Queue\Monitoring\Storage;

use Bitrix\Main\Type\DateTime;
use Bsi\Queue\Exception\LogicException;
use Bsi\Queue\Exception\RuntimeException;
use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\Entity\BitrixStatTable;
use Bsi\Queue\Monitoring\SentMessageStats;
use Bsi\Queue\Stamp\UuidStamp;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixStorage implements StorageInterface
{
    public function pushSentMessageStats(SentMessageStats $stats): void
    {
        /** @var UuidStamp|null $uuidStamp */
        $uuidStamp = $stats->getEnvelope()->last(UuidStamp::class);
        if ($uuidStamp === null) {
            throw new LogicException('No UuidStamp found on the Envelope.');
        }

        $message = $stats->getEnvelope()->getMessage();
        $stamps = $stats->getEnvelope()->all(BusNameStamp::class);
        $busNames = [];
        /** @var BusNameStamp $stamp */
        foreach ($stamps as $stamp) {
            $busNames[] = $stamp->getBusName();
        }

        $result = BitrixStatTable::add([
            'UUID' => $uuidStamp->getUuid()->toString(),
            'MESSAGE' => get_class($message),
            'STATUS' => SentMessageStats::STATUS,
            'BUSES' => $busNames,
        ]);
        if (!$result->isSuccess()) {
            throw new RuntimeException(implode("\n", $result->getErrorMessages()));
        }
    }

    public function pushConsumedMessageStats(ConsumedMessageStats $stats): void
    {
        /** @var UuidStamp|null $uuidStamp */
        $uuidStamp = $stats->getEnvelope()->last(UuidStamp::class);
        if ($uuidStamp === null) {
            throw new LogicException('No UuidStamp found on the Envelope.');
        }
        $uuid = $uuidStamp->getUuid()->toString();

        $row = BitrixStatTable::getRowByUuid($uuid);
        if ($row === null) {
            throw new RuntimeException(sprintf('Envelope with uuid "%s" not found.', $uuid));
        }

        $data = [
            'STATUS' => $stats->getStatus(),
            'TRANSPORT' => $stats->getTransport(),
        ];
        if ($stats->getStatus() === ConsumedMessageStats::STATUS_RECEIVED) {
            $data['RECEIVED_AT'] = new DateTime();
        } elseif ($stats->getStatus() === ConsumedMessageStats::STATUS_HANDLED) {
            $data['HANDLED_AT'] = new DateTime();
        } elseif ($stats->getStatus() === ConsumedMessageStats::STATUS_FAILED) {
            $data['FAILED_AT'] = new DateTime();
        }
        $result = BitrixStatTable::update($row['ID'], $data);
        if (!$result->isSuccess()) {
            throw new RuntimeException(implode("\n", $result->getErrorMessages()));
        }
    }
}
