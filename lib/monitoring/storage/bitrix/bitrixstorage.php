<?php

namespace Bsi\Queue\Monitoring\Storage\Bitrix;

use Bitrix\Main\Type\DateTime;
use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\Entity\StatTable;
use Bsi\Queue\Monitoring\SentMessageStats;
use Bsi\Queue\Monitoring\Storage\StorageInterface;
use Bsi\Queue\Stamp\UniqueIdStamp;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\RuntimeException;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixStorage implements StorageInterface
{
    public function pushSent(SentMessageStats $stats): void
    {
        /** @var UniqueIdStamp|null $uniqueIdStamp */
        $uniqueIdStamp = $stats->getEnvelope()->last(UniqueIdStamp::class);
        if ($uniqueIdStamp === null) {
            throw new LogicException('No UniqueIdStamp found on the Envelope.');
        }

        $message = $stats->getEnvelope()->getMessage();
        $stamps = $stats->getEnvelope()->all(BusNameStamp::class);
        $busNames = [];
        /** @var BusNameStamp $stamp */
        foreach ($stamps as $stamp) {
            $busNames[] = $stamp->getBusName();
        }

        $result = StatTable::add([
            'UID' => $uniqueIdStamp->getUniqueId(),
            'MESSAGE' => get_class($message),
            'STATUS' => SentMessageStats::STATUS,
            'BUSES' => $busNames,
        ]);
        if (!$result->isSuccess()) {
            throw new RuntimeException(implode("\n", $result->getErrorMessages()));
        }
    }

    public function pushConsumed(ConsumedMessageStats $stats): void
    {
        /** @var UniqueIdStamp|null $uniqueIdStamp */
        $uniqueIdStamp = $stats->getEnvelope()->last(UniqueIdStamp::class);
        if ($uniqueIdStamp === null) {
            throw new LogicException('No UniqueIdStamp found on the Envelope.');
        }

        $row = StatTable::getRow([
            'select' => ['ID'],
            'filter' => ['UID' => $uniqueIdStamp->getUniqueId()],
        ]);
        if (!$row) {
            throw new RuntimeException(sprintf('Envelope with unique id "%s" not found', $uniqueIdStamp->getUniqueId()));
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
        $result = StatTable::update($row['ID'], $data);
        if (!$result->isSuccess()) {
            throw new RuntimeException(implode("\n", $result->getErrorMessages()));
        }
    }
}
