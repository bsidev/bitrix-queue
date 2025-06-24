<?php

declare(strict_types=1);

namespace Bsi\Queue\Tests\Fixtures\Transport;

use Bsi\Queue\Tests\Fixtures\DummyService;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class DummyTransportFactory implements TransportFactoryInterface
{
    private ?DummyService $service;
    public function __construct(?DummyService $service = null)
    {
        $this->service = $service;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $this->service?->handle();
        return new DummyTransport();
    }

    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'dummy:');
    }
}
