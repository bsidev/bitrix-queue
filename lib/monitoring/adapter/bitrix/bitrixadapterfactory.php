<?php

namespace Bsi\Queue\Monitoring\Adapter\Bitrix;

use Bsi\Queue\Monitoring\Adapter\AdapterFactoryInterface;
use Bsi\Queue\Monitoring\Adapter\AdapterInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixAdapterFactory implements AdapterFactoryInterface
{
    public function createAdapter(string $name, array $options): AdapterInterface
    {
        return new BitrixAdapter();
    }

    public function supports(string $name, array $options): bool
    {
        return $name === 'bitrix';
    }
}
