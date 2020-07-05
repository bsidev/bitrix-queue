<?php

namespace Bsi\Queue\Tests\Fixtures;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class DummyMessageHandler implements MessageSubscriberInterface
{
    public function __invoke(DummyMessage $message)
    {
    }

    public static function getHandledMessages(): iterable
    {
        yield DummyMessage::class;
    }
}
