<?php

namespace Bsi\Queue\Tests\Monitoring\Adapter\Bitrix;

use Bsi\Queue\Monitoring\Adapter\Bitrix\BitrixAdapter;
use Bsi\Queue\Monitoring\Repository\BitrixMetricRepository;
use Bsi\Queue\Monitoring\Storage\BitrixStorage;
use Bsi\Queue\Tests\AbstractTestCase;

class BitrixAdapterTest extends AbstractTestCase
{
    public function testGetStorage(): void
    {
        $adapter = new BitrixAdapter();
        $this->assertInstanceOf(BitrixStorage::class, $adapter->getStorage());
    }

    public function testGetMetricRepository(): void
    {
        $adapter = new BitrixAdapter();
        $this->assertInstanceOf(BitrixMetricRepository::class, $adapter->getMetricRepository());
    }
}
