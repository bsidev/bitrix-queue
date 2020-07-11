<?php

namespace Bsi\Queue\Tests\Monitoring\Adapter\Bitrix;

use Bsi\Queue\Monitoring\Adapter\Bitrix\BitrixAdapter;
use Bsi\Queue\Monitoring\Adapter\Bitrix\BitrixAdapterFactory;
use Bsi\Queue\Tests\AbstractTestCase;

class BitrixAdapterFactoryTest extends AbstractTestCase
{
    public function testCreateAdapter(): void
    {
        $factory = new BitrixAdapterFactory();
        $adapter = $factory->createAdapter('bitrix', []);
        $this->assertInstanceOf(BitrixAdapter::class, $adapter);
    }

    public function testSupports(): void
    {
        $factory = new BitrixAdapterFactory();
        $this->assertTrue($factory->supports('bitrix', []));
        $this->assertFalse($factory->supports('redis', []));
    }
}
