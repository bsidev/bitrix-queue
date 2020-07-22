<?php

namespace Bsi\Queue\Tests\Middleware;

use Bsi\Queue\Middleware\AddUuidStampMiddleware;
use Bsi\Queue\Stamp\UuidStamp;
use Bsi\Queue\Tests\AbstractTestCase;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;

class AddUuidStampMiddlewareTest extends AbstractTestCase
{
    public function testMiddleware(): void
    {
        $middleware = new AddUuidStampMiddleware();
        $envelope = new Envelope(new DummyMessage('hello'));

        $finalEnvelope = $middleware->handle($envelope, $this->getStackMock());
        /** @var UuidStamp $uuidStamp */
        $uuidStamp = $finalEnvelope->last(UuidStamp::class);
        $this->assertNotNull($uuidStamp);

        // the stamp should not be added over and over again
        $finalEnvelope = $middleware->handle($finalEnvelope, $this->getStackMock());
        $this->assertCount(1, $finalEnvelope->all(UuidStamp::class));
    }

    private function getStackMock(bool $nextIsCalled = true)
    {
        if (!$nextIsCalled) {
            $stack = Mockery::mock(StackInterface::class);
            $stack->shouldReceive('next')->never();

            return $stack;
        }

        $nextMiddleware = Mockery::mock(MiddlewareInterface::class);
        $nextMiddleware->shouldReceive('handle')
            ->once()
            ->andReturnUsing(function (Envelope $envelope, StackInterface $stack): Envelope {
                return $envelope;
            });

        return new StackMiddleware($nextMiddleware);
    }
}
