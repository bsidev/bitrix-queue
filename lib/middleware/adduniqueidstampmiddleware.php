<?php

namespace Bsi\Queue\Middleware;

use Bsi\Queue\Stamp\UniqueIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class AddUniqueIdStampMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($envelope->last(UniqueIdStamp::class) === null) {
            $envelope = $envelope->with(new UniqueIdStamp());
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
