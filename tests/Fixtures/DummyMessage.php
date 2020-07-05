<?php

namespace Bsi\Queue\Tests\Fixtures;

class DummyMessage
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}
