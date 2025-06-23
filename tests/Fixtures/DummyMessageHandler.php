<?php

namespace Bsi\Queue\Tests\Fixtures;

class DummyMessageHandler
{
    public function __invoke(DummyMessage $message)
    {
    }
}
