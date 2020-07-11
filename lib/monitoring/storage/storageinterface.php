<?php

namespace Bsi\Queue\Monitoring\Storage;

use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\SentMessageStats;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface StorageInterface
{
    public function pushSentMessageStats(SentMessageStats $stats): void;

    public function pushConsumedMessageStats(ConsumedMessageStats $stats): void;
}
