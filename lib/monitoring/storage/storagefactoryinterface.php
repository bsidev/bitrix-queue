<?php

namespace Bsi\Queue\Monitoring\Storage;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface StorageFactoryInterface
{
    public function createTransport(string $dsn, array $options): StorageInterface;

    public function supports(string $dsn, array $options): bool;
}
