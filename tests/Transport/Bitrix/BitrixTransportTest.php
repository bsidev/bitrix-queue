<?php

namespace Bsi\Queue\Tests\Transport\Bitrix;

use Bsi\Queue\Tests\AbstractTestCase;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Bsi\Queue\Transport\Bitrix\BitrixTransport;
use Bsi\Queue\Transport\Bitrix\Connection;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class BitrixTransportTest extends AbstractTestCase
{
    public function testItIsATransport(): void
    {
        $transport = $this->getTransport();

        $this->assertInstanceOf(TransportInterface::class, $transport);
    }

    public function testReceivesMessages(): void
    {
        $transport = $this->getTransport(
            $serializer = Mockery::mock(SerializerInterface::class),
            $connection = Mockery::mock(Connection::class)
        );

        $decodedMessage = new DummyMessage('foobar');

        $bitrixEnvelope = [
            'ID' => '5',
            'BODY' => 'body',
            'HEADERS' => ['foo' => 'bar'],
        ];

        $serializer->shouldReceive('decode')
            ->with(['body' => 'body', 'headers' => ['foo' => 'bar']])
            ->andReturn(new Envelope($decodedMessage));
        $connection->shouldReceive('get')->andReturn($bitrixEnvelope);

        $envelopes = $transport->get();
        $this->assertSame($decodedMessage, $envelopes[0]->getMessage());
    }

    private function getTransport(
        SerializerInterface $serializer = null,
        Connection $connection = null
    ): BitrixTransport {
        $serializer = $serializer ?: $this->createMock(SerializerInterface::class);
        $connection = $connection ?: $this->createMock(Connection::class);

        return new BitrixTransport($connection, $serializer);
    }
}
