<?php

namespace Bsi\Queue\Tests\Monitoring\Storage;

use Bsi\Queue\Monitoring\ConsumedMessageStats;
use Bsi\Queue\Monitoring\Entity\BitrixStatTable;
use Bsi\Queue\Monitoring\SentMessageStats;
use Bsi\Queue\Monitoring\Storage\BitrixStorage;
use Bsi\Queue\Stamp\UuidStamp;
use Bsi\Queue\Tests\AbstractTestCase;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

class BitrixStorageTest extends AbstractTestCase
{
    public function testPushSentMessageStats(): void
    {
        $statTable = Mockery::mock('alias:' . BitrixStatTable::class);
        $statTable->shouldReceive('add')->andReturnUsing(function ($data) {
            $this->assertSame('stdClass', $data['MESSAGE']);
            $this->assertSame(SentMessageStats::STATUS, $data['STATUS']);
            $this->assertEquals(['dummy'], $data['BUSES']);

            return $this->getBitrixOrmResultMock(true);
        });

        $storage = new BitrixStorage();

        $envelope = new Envelope(new \stdClass(), [
            new BusNameStamp('dummy'),
            new UuidStamp(),
        ]);
        $stats = new SentMessageStats($envelope);
        $storage->pushSentMessageStats($stats);
    }

    public function testPushConsumedMessageStats(): void
    {
        $statTable = Mockery::mock('alias:' . BitrixStatTable::class);
        $statTable->shouldReceive('getRowByUuid')->andReturn(['ID' => '1']);
        $statTable->shouldReceive('isSuccess')->andReturn(true);
        $statTable->shouldReceive('update')->andReturnUsing(function ($id, $data) {
            $this->assertSame('1', $id);
            $this->assertSame(ConsumedMessageStats::STATUS_HANDLED, $data['STATUS']);
            $this->assertSame('async', $data['TRANSPORT']);

            return $this->getBitrixOrmResultMock(true);
        });

        $storage = new BitrixStorage();

        $envelope = new Envelope(new \stdClass(), [
            new BusNameStamp('dummy'),
            new UuidStamp(),
        ]);
        $stats = new ConsumedMessageStats($envelope, 'async', ConsumedMessageStats::STATUS_HANDLED);
        $storage->pushConsumedMessageStats($stats);
    }
}
