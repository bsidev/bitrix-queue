<?php

namespace Bsi\Queue\Monitoring\Adapter;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface AdapterFactoryInterface
{
    public function createAdapter(string $name, array $options): AdapterInterface;

    public function supports(string $name, array $options): bool;
}
