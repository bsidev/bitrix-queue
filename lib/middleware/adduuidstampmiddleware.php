<?php

namespace Bsi\Queue\Middleware;

use Bsi\Queue\Stamp\UuidStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class AddUuidStampMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($envelope->last(UuidStamp::class) === null) {
            $envelope = $envelope->with(new UuidStamp());
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
