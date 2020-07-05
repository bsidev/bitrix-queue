<?php

namespace Bsi\Queue\Tests\Transport\Bitrix;

use Bsi\Queue\Tests\AbstractTestCase;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Bsi\Queue\Transport\Bitrix\BitrixReceivedStamp;
use Bsi\Queue\Transport\Bitrix\BitrixReceiver;
use Bsi\Queue\Transport\Bitrix\Connection;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer as SerializerComponent;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class BitrixReceiverTest extends AbstractTestCase
{
    public function testItReturnsTheDecodedMessageToTheHandler(): void
    {
        $serializer = $this->createSerializer();

        $bitrixEnvelope = $this->createBitrixEnvelope();
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('get')
            ->once()
            ->andReturn($bitrixEnvelope);

        $receiver = new BitrixReceiver($connection, $serializer);
        $actualEnvelopes = $receiver->get();
        $this->assertCount(1, $actualEnvelopes);
        /** @var Envelope $actualEnvelope */
        $actualEnvelope = $actualEnvelopes[0];
        $this->assertEquals(new DummyMessage('foo'), $actualEnvelopes[0]->getMessage());

        /** @var BitrixReceivedStamp $bitrixReceivedStamp */
        $bitrixReceivedStamp = $actualEnvelope->last(BitrixReceivedStamp::class);
        $this->assertNotNull($bitrixReceivedStamp);
        $this->assertSame(1, $bitrixReceivedStamp->getId());

        /** @var TransportMessageIdStamp $transportMessageIdStamp */
        $transportMessageIdStamp = $actualEnvelope->last(TransportMessageIdStamp::class);
        $this->assertNotNull($transportMessageIdStamp);
        $this->assertSame(1, $transportMessageIdStamp->getId());
    }

    public function testItRejectTheMessageIfThereIsAMessageDecodingFailedException(): void
    {
        $this->expectException(MessageDecodingFailedException::class);
        $serializer = Mockery::mock(PhpSerializer::class);
        $serializer->shouldReceive('decode')->andThrow(new MessageDecodingFailedException());

        $bitrixEnvelope = $this->createBitrixEnvelope();
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('get')->andReturn($bitrixEnvelope);
        $connection->shouldReceive('reject')->once();

        $receiver = new BitrixReceiver($connection, $serializer);
        $receiver->get();
    }

    public function testFind(): void
    {
        $serializer = $this->createSerializer();

        $bitrixEnvelope = $this->createBitrixEnvelope();
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('find')->andReturn($bitrixEnvelope);

        $receiver = new BitrixReceiver($connection, $serializer);
        $actualEnvelope = $receiver->find(10);
        $this->assertEquals(new DummyMessage('foo'), $actualEnvelope->getMessage());
    }

    public function testAll(): void
    {
        $serializer = $this->createSerializer();

        $bitrixEnvelope1 = $this->createBitrixEnvelope();
        $bitrixEnvelope2 = $this->createBitrixEnvelope();
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('findAll')->andReturn([$bitrixEnvelope1, $bitrixEnvelope2]);

        $receiver = new BitrixReceiver($connection, $serializer);
        $actualEnvelopes = iterator_to_array($receiver->all(50));
        $this->assertCount(2, $actualEnvelopes);
        $this->assertEquals(new DummyMessage('foo'), $actualEnvelopes[0]->getMessage());
    }

    private function createBitrixEnvelope(): array
    {
        return [
            'ID' => 1,
            'BODY' => '{"message":"foo"}',
            'HEADERS' => [
                'type' => DummyMessage::class,
            ],
        ];
    }

    private function createSerializer(): Serializer
    {
        return new Serializer(
            new SerializerComponent\Serializer([new ObjectNormalizer()], ['json' => new JsonEncoder()])
        );
    }
}
