<?php

namespace Bsi\Queue\Tests\Transport\Bitrix;

use Bsi\Queue\Entity\MessageTable;
use Bsi\Queue\Tests\AbstractTestCase;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Bsi\Queue\Transport\Bitrix\Connection;
use Mockery;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Exception\TransportException;

class ConnectionTest extends AbstractTestCase
{
    public function testGet(): void
    {
        $this->getDataManagerMock();
        $messageTable = $this->getMessageTableMock();
        $messageTable->shouldReceive('update')
            ->andReturnUsing(function () {
                return $this->getBitrixOrmResultMock(true);
            });
        $result = $this->getResultMock([
            'ID' => 1,
            'BODY' => '{"message":"foo"}',
            'HEADERS' => ['type' => DummyMessage::class],
        ]);
        $this->getQueryMock($result);

        $connection = new Connection();
        $bitrixEnvelope = $connection->get();
        $this->assertEquals(1, $bitrixEnvelope['ID']);
        $this->assertEquals('{"message":"foo"}', $bitrixEnvelope['BODY']);
        $this->assertEquals(['type' => DummyMessage::class], $bitrixEnvelope['HEADERS']);
    }

    public function testGetIfQueueIsEmpty(): void
    {
        $this->getDataManagerMock();
        $this->getMessageTableMock();
        $result = $this->getResultMock(null);
        $this->getQueryMock($result);

        $connection = new Connection();
        $bitrixEnvelope = $connection->get();
        $this->assertNull($bitrixEnvelope);
    }

    public function testAcknowledgeMessageIfItsNotExists(): void
    {
        $this->expectException(TransportException::class);

        $messageTable = $this->getMessageTableMock();
        $messageTable->shouldReceive('delete')
            ->andReturnUsing(function () {
                return $this->getBitrixOrmResultMock(false, ['Invalid ID']);
            });

        $connection = new Connection();
        $connection->ack(0);
    }

    public function testInvalidConfigurationKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown option found: [dummy_option]. Allowed options are [queue_name, redeliver_timeout]');
        new Connection(['dummy_option' => 'foobar']);
    }

    public function testFind(): void
    {
        $this->getDataManagerMock();
        $id = 1;
        $messageTable = $this->getMessageTableMock();
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
        $this->getDataManagerMock();
        $query = $this->getQueryMock();
        $query->shouldReceive('fetchAll')->andReturn([
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
        $this->getMessageTableMock();

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
        $this->getQueryMock($this->getResultMock(['CNT' => 2]));
        $this->getMessageTableMock();

        $connection = new Connection();
        $count = $connection->getMessageCount();
        $this->assertSame(2, $count);
    }

    private function getMessageTableMock(): Mockery\MockInterface
    {
        $mock = Mockery::mock('alias:' . MessageTable::class);

        $mock->shouldReceive('getConnectionName')->andReturn('default');
        $mock->shouldReceive('query')->andReturnUsing(function () {
            /** @noinspection PhpUndefinedClassInspection */
            return new \Bitrix\Main\ORM\Query\Query();
        });

        return $mock;
    }

    private function getDataManagerMock(): Mockery\MockInterface
    {
        $mock = Mockery::mock('overload:Bitrix\Main\ORM\Data\DataManager');

        $mock->shouldReceive('getConnectionName')->andReturn('default');

        return $mock;
    }

    private function getQueryMock($result = null): Mockery\MockInterface
    {
        $mock = Mockery::mock('overload:Bitrix\Main\ORM\Query\Query');

        $mock->shouldReceive('setSelect')->andReturnSelf();
        $mock->shouldReceive('filter')->andReturnSelf();
        $mock->shouldReceive('logic')->andReturnSelf();
        $mock->shouldReceive('where')->andReturnSelf();
        $mock->shouldReceive('whereNull')->andReturnSelf();
        $mock->shouldReceive('addOrder')->andReturnSelf();
        $mock->shouldReceive('setLimit')->andReturnSelf();
        $mock->shouldReceive('exec')->andReturn($result);
        $mock->shouldReceive('expr')->andReturnUsing(function () {
            return new class () {
                public function count(): void
                {
                }
            };
        });

        return $mock;
    }

    private function getResultMock($expectedResult): Mockery\MockInterface
    {
        $mock = Mockery::mock('QueryResult');

        $mock->shouldReceive('fetch')->andReturn($expectedResult);

        return $mock;
    }
}
