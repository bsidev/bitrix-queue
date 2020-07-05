<?php

namespace Bsi\Queue\Tests\Monitoring\Storage;

use Bitrix\Main\Type\DateTime;
use Bsi\Queue\Monitoring\Adapter\Bitrix\BitrixMessageStatTable;
use Bsi\Queue\Monitoring\MessageStatuses;
use Bsi\Queue\Monitoring\Storage\BitrixStorage;
use Bsi\Queue\Stamp\UuidStamp;
use Bsi\Queue\Tests\AbstractTestCase;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

class BitrixStorageTest extends AbstractTestCase
{
    public function testPushSentMessageStats(): void
    {
        $uuidStamp = new UuidStamp();

        $serializer = new PhpSerializer();
        $storage = new BitrixStorage($serializer);

        $envelope = new Envelope(new \stdClass(), [
            $uuidStamp,
        ]);

        $encodedEnvelope = $serializer->encode($envelope);

        $statTable = Mockery::mock('alias:' . BitrixMessageStatTable::class);
        $statTable->shouldReceive('add')->andReturnUsing(function ($data) use ($uuidStamp, $encodedEnvelope) {
            $this->assertSame($uuidStamp->getUuid()->toString(), $data['UUID']);
            $this->assertSame('stdClass', $data['MESSAGE']);
            $this->assertSame(MessageStatuses::SENT, $data['STATUS']);
            $this->assertSame($encodedEnvelope['body'], $data['BODY']);
            $this->assertSame([], $data['HEADERS']);

            return $this->getBitrixOrmResultMock(true);
        });

        $storage->pushSentMessageStats($envelope);
    }

    public function testPushReceivedMessageStats(): void
    {
        $serializer = new PhpSerializer();
        $storage = new BitrixStorage($serializer);

        $envelope = new Envelope(new \stdClass(), [
            new UuidStamp(),
        ]);

        $encodedEnvelope = $serializer->encode($envelope);

        $statTable = Mockery::mock('alias:' . BitrixMessageStatTable::class);
        $statTable->shouldReceive('getRowByUuid')->andReturn(['ID' => '1']);
        $statTable->shouldReceive('isSuccess')->andReturn(true);
        $statTable->shouldReceive('update')->andReturnUsing(function ($id, $data) use ($encodedEnvelope) {
            $this->assertSame('1', $id);
            $this->assertSame(MessageStatuses::RECEIVED, $data['STATUS']);
            $this->assertSame('async', $data['TRANSPORT_NAME']);
            $this->assertSame($encodedEnvelope['body'], $data['BODY']);
            $this->assertSame([], $data['HEADERS']);
            $this->assertInstanceOf(DateTime::class, $data['RECEIVED_AT']);

            return $this->getBitrixOrmResultMock(true);
        });

        $storage->pushConsumedMessageStats($envelope, MessageStatuses::RECEIVED, 'async');
    }

    public function testPushHandledMessageStats(): void
    {
        $serializer = new PhpSerializer();
        $storage = new BitrixStorage($serializer);

        $envelope = new Envelope(new \stdClass(), [
            new UuidStamp(),
        ]);

        $encodedEnvelope = $serializer->encode($envelope);

        $statTable = Mockery::mock('alias:' . BitrixMessageStatTable::class);
        $statTable->shouldReceive('getRowByUuid')->andReturn(['ID' => '1']);
        $statTable->shouldReceive('isSuccess')->andReturn(true);
        $statTable->shouldReceive('update')->andReturnUsing(function ($id, $data) use ($encodedEnvelope) {
            $this->assertSame('1', $id);
            $this->assertSame(MessageStatuses::HANDLED, $data['STATUS']);
            $this->assertSame('async', $data['TRANSPORT_NAME']);
            $this->assertSame($encodedEnvelope['body'], $data['BODY']);
            $this->assertSame([], $data['HEADERS']);
            $this->assertInstanceOf(DateTime::class, $data['HANDLED_AT']);

            return $this->getBitrixOrmResultMock(true);
        });

        $storage->pushConsumedMessageStats($envelope, MessageStatuses::HANDLED, 'async');
    }

    public function testPushFailedMessageStats(): void
    {
        $serializer = new PhpSerializer();
        $storage = new BitrixStorage($serializer);

        $envelope = new Envelope(new \stdClass(), [
            new UuidStamp(),
        ]);

        $encodedEnvelope = $serializer->encode($envelope);

        $statTable = Mockery::mock('alias:' . BitrixMessageStatTable::class);
        $statTable->shouldReceive('getRowByUuid')->andReturn(['ID' => '1']);
        $statTable->shouldReceive('isSuccess')->andReturn(true);
        $statTable->shouldReceive('update')->andReturnUsing(function ($id, $data) use ($encodedEnvelope) {
            $this->assertSame('1', $id);
            $this->assertSame(MessageStatuses::FAILED, $data['STATUS']);
            $this->assertSame('async', $data['TRANSPORT_NAME']);
            $this->assertSame($encodedEnvelope['body'], $data['BODY']);
            $this->assertSame([], $data['HEADERS']);
            $this->assertInstanceOf(DateTime::class, $data['FAILED_AT']);
            $this->assertStringContainsString('RuntimeException: test', $data['ERROR']);

            return $this->getBitrixOrmResultMock(true);
        });

        $storage->pushConsumedMessageStats($envelope, MessageStatuses::FAILED, 'async', new \RuntimeException('test'));
    }
}
