<?php

namespace Bsi\Queue\Tests\Fixtures;

class DummyMessageHandler
{
    private ?DummyService $service;

    public function __construct(?DummyService $service = null)
    {
        $this->service = $service;
    }

    public function __invoke(DummyMessage $message): void
    {
        $this->service?->handle();
    }
}
