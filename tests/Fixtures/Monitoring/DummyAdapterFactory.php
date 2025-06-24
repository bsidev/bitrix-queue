<?php

declare(strict_types=1);

namespace Bsi\Queue\Tests\Fixtures\Monitoring;

use Bsi\Queue\Tests\Fixtures\DummyService;
use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Monitoring\Adapter\AdapterFactoryInterface;

class DummyAdapterFactory implements AdapterFactoryInterface
{
    private ?DummyService $service;
    public function __construct(?DummyService $service = null)
    {
        $this->service = $service;
    }

    public function createAdapter(string $name, array $options): AdapterInterface
    {
        $this->service?->handle();
        return new DummyAdapter();
    }

    public function supports(string $name, array $options): bool
    {
        return $name === 'dummy';
    }
}
