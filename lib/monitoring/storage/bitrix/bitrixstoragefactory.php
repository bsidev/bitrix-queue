<?php

namespace Bsi\Queue\Monitoring\Storage\Bitrix;

use Bsi\Queue\Monitoring\Storage\StorageFactoryInterface;
use Bsi\Queue\Monitoring\Storage\StorageInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixStorageFactory implements StorageFactoryInterface
{
    public function createTransport(string $dsn, array $options): StorageInterface
    {
        return new BitrixStorage();
    }

    public function supports(string $dsn, array $options): bool
    {
        return strpos($dsn, 'bitrix://') === 0;
    }
}
