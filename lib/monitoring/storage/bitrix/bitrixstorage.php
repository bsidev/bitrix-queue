<?php

namespace Bsi\Queue\Monitoring\Storage\Bitrix;

use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\SentMessageStats;
use Bsi\Queue\Monitoring\Storage\StorageInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixStorage implements StorageInterface
{
    public function pushSent(SentMessageStats $stats): void
    {
        $message = $stats->getEnvelope()->getMessage();
        $stamps = $stats->getEnvelope()->all(BusNameStamp::class);
        $busNames = [];
        /** @var BusNameStamp $stamp */
        foreach ($stamps as $stamp) {
            $busNames[] = $stamp->getBusName();
        }

        StatTable::add([
            'MESSAGE' => get_class($message),
            'STATUS' => 'sent',
            'BUSES' => $busNames,
        ]);
    }

    public function pushConsumed(ConsumedMessageStats $stats): void
    {
        $message = $stats->getEnvelope()->getMessage();
        $stamps = $stats->getEnvelope()->all(BusNameStamp::class);
        $busNames = [];
        /** @var BusNameStamp $stamp */
        foreach ($stamps as $stamp) {
            $busNames[] = $stamp->getBusName();
        }

        StatTable::add([
            'MESSAGE' => get_class($message),
            'STATUS' => $stats->getStatus(),
            'TRANSPORT' => $stats->getTransport(),
            'BUSES' => $busNames,
        ]);
    }
}
