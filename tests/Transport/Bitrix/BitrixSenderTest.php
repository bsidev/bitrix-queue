<?php

namespace Bsi\Queue\Tests\Transport\Bitrix;

use Bsi\Queue\Tests\AbstractTestCase;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Bsi\Queue\Transport\Bitrix\BitrixSender;
use Bsi\Queue\Transport\Bitrix\Connection;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;

class BitrixSenderTest extends AbstractTestCase
{
    public function testSend(): void
    {
        $envelope = new Envelope(new DummyMessage('Hello'));

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('send')
            ->once()
            ->andReturn(11);

        $sender = new BitrixSender($connection);
        $actualEnvelope = $sender->send($envelope);

        /** @var TransportMessageIdStamp $transportMessageIdStamp */
        $transportMessageIdStamp = $actualEnvelope->last(TransportMessageIdStamp::class);
        $this->assertNotNull($transportMessageIdStamp);
        $this->assertSame(11, $transportMessageIdStamp->getId());
    }
}
