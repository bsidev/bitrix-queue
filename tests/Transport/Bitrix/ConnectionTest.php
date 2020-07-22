<?php

namespace Bsi\Queue\Tests\Transport\Bitrix;

use Bsi\Queue\Tests\AbstractTestCase;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Bsi\Queue\Transport\Bitrix\Connection;
use Bsi\Queue\Transport\Bitrix\MessageTable;
use Mockery;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Exception\TransportException;

class ConnectionTest extends AbstractTestCase
{
    public function testGet(): void
    {
        $query = $this->getQueryMock();
        $query->shouldReceive('exec->fetch')->andReturn([
            'ID' => 1,
            'BODY' => '{"message":"foo"}',
            'HEADERS' => ['type' => DummyMessage::class],
        ]);

        $messageTable = $this->getMessageTableMock($query);
        $messageTable->shouldReceive('update->isSuccess')->andReturn(true);

        $connection = new Connection();
        $bitrixEnvelope = $connection->get();
        $this->assertEquals(1, $bitrixEnvelope['ID']);
        $this->assertEquals('{"message":"foo"}', $bitrixEnvelope['BODY']);
        $this->assertEquals(['type' => DummyMessage::class], $bitrixEnvelope['HEADERS']);
    }

    public function testGetIfQueueIsEmpty(): void
    {
        $query = $this->getQueryMock();
        $query->shouldReceive('exec->fetch')->andReturnNull();

        $this->getMessageTableMock($query);

        $connection = new Connection();
        $bitrixEnvelope = $connection->get();
        $this->assertNull($bitrixEnvelope);
    }

    public function testAcknowledgeMessageIfItsNotExists(): void
    {
        $this->expectException(TransportException::class);

        $query = $this->getQueryMock();
        $messageTable = $this->getMessageTableMock($query);
        $messageTable->shouldReceive('delete->isSuccess')->andReturnFalse();
        $messageTable->shouldReceive('delete->getErrorMessages')->andReturn(['Invalid ID']);

        $connection = new Connection();
        $connection->ack(0);
    }

    public function testInvalidConfigurationKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Unknown option found/');
        Connection::buildConfiguration('bitrix://default?dummy_option=1');
    }

    public function testFind(): void
    {
        $id = 1;
        $query = $this->getQueryMock();
        $messageTable = $this->getMessageTableMock($query);
        $messageTable->shouldReceive('getRowById')
            ->andReturn([
                'ID' => $id,
                'BODY' => '{"message":"foo"}',
                'HEADERS' => ['type' => DummyMessage::class],
            ]);

        $connection = new Connection();
        $bitrixEnvelope = $connection->find($id);
        $this->assertEquals($id, $bitrixEnvelope['ID']);
        $this->assertEquals('{"message":"foo"}', $bitrixEnvelope['BODY']);
        $this->assertEquals(['type' => DummyMessage::class], $bitrixEnvelope['HEADERS']);
    }

    public function testFindAll(): void
    {
        $query = $this->getQueryMock();
        $query->shouldReceive('exec->fetchAll')->andReturn([
            [
                'ID' => 1,
                'BODY' => '{"message":"foo"}',
                'HEADERS' => ['type' => DummyMessage::class],
            ],
            [
                'ID' => 2,
                'BODY' => '{"message":"bar"}',
                'HEADERS' => ['type' => DummyMessage::class],
            ],
        ]);
        $this->getMessageTableMock($query);

        $connection = new Connection();
        $bitrixEnvelopes = $connection->findAll();

        $this->assertEquals(1, $bitrixEnvelopes[0]['ID']);
        $this->assertEquals('{"message":"foo"}', $bitrixEnvelopes[0]['BODY']);
        $this->assertEquals(['type' => DummyMessage::class], $bitrixEnvelopes[0]['HEADERS']);

        $this->assertEquals(2, $bitrixEnvelopes[1]['ID']);
        $this->assertEquals('{"message":"bar"}', $bitrixEnvelopes[1]['BODY']);
        $this->assertEquals(['type' => DummyMessage::class], $bitrixEnvelopes[1]['HEADERS']);
    }

    public function testGetMessageCount(): void
    {
        $query = $this->getQueryMock();
        $query->shouldReceive('exec->fetch')->andReturn(['CNT' => 2]);
        $this->getMessageTableMock($query);

        $connection = new Connection();
        $count = $connection->getMessageCount();
        $this->assertSame(2, $count);
    }

    private function getMessageTableMock(Mockery\MockInterface $query): Mockery\MockInterface
    {
        $mock = Mockery::mock('alias:' . MessageTable::class);

        $mock->shouldReceive('getConnectionName');
        $mock->shouldReceive('query')->andReturnUsing(function () use ($query) {
            return $query;
        });

        return $mock;
    }

    private function getQueryMock(): Mockery\MockInterface
    {
        $mock = Mockery::mock('overload:Bitrix\Main\ORM\Query\Query');

        $mock->shouldReceive('setSelect')->andReturnSelf();
        $mock->shouldReceive('filter')->andReturnSelf();
        $mock->shouldReceive('logic')->andReturnSelf();
        $mock->shouldReceive('where')->andReturnSelf();
        $mock->shouldReceive('whereNull')->andReturnSelf();
        $mock->shouldReceive('addOrder')->andReturnSelf();
        $mock->shouldReceive('setLimit')->andReturnSelf();
        $mock->shouldReceive('expr->count');

        return $mock;
    }
}
