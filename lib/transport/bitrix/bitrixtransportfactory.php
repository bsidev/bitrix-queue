<?php

namespace Bsi\Queue\Transport\Bitrix;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixTransportFactory implements TransportFactoryInterface
{
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        unset($options['transport_name']);

        if (preg_match('/:\/\/$/', $dsn)) {
            $configuration = $options;
        } else {
            $configuration = Connection::buildConfiguration($dsn, $options);
        }

        return new BitrixTransport(new Connection($configuration), $serializer);
    }

    public function supports(string $dsn, array $options): bool
    {
        return strpos($dsn, 'bitrix://') === 0;
    }
}
