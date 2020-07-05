<?php

namespace Bsi\Queue\Tests\Transport\Bitrix;

use Bsi\Queue\Tests\AbstractTestCase;
use Bsi\Queue\Transport\Bitrix\BitrixTransport;
use Bsi\Queue\Transport\Bitrix\BitrixTransportFactory;
use Bsi\Queue\Transport\Bitrix\Connection;
use Mockery;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class BitrixTransportFactoryTest extends AbstractTestCase
{
    public function testSupports(): void
    {
        $factory = new BitrixTransportFactory();

        $this->assertTrue($factory->supports('bitrix://', []));
        $this->assertFalse($factory->supports('redis://localhost', []));
    }

    public function testCreateTransport(): void
    {
        $factory = new BitrixTransportFactory();
        $serializer = Mockery::mock(SerializerInterface::class);

        $this->assertEquals(
            new BitrixTransport(new Connection(), $serializer),
            $factory->createTransport('bitrix://', [], $serializer)
        );
    }
}
