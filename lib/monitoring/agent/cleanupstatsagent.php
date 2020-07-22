<?php

namespace Bsi\Queue\Monitoring\Agent;

use Bitrix\Main\Config\Option;
use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Queue;

class CleanUpStatsAgent
{
    public static function run(): string
    {
        $lifetime = (int) Option::get('bsi.queue', 'stats_lifetime', 365);

        try {
            $adapter = Queue::getInstance()->getContainer()->get(AdapterInterface::class);
            if ($adapter instanceof AdapterInterface) {
                $adapter->getStorage()->cleanUpStats($lifetime);
            }
        } catch (\Throwable $e) {
        }

        return static::class . '::run();';
    }
}
