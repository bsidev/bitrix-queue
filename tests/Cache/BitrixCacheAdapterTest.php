<?php

namespace Bsi\Queue\Tests\Cache;

use Bsi\Queue\Cache\BitrixCacheAdapter;
use Bsi\Queue\Tests\AbstractTestCase;

class BitrixCacheAdapterTest extends AbstractTestCase
{
    public function testGet(): void
    {
        $cache = $this->createCachePool();

        $item = $cache->getItem('foo');
        $this->assertSame('foo', $item->getKey());
        $this->assertNull($item->get());
    }

    public function testSet(): void
    {
        $cache = $this->createCachePool();

        $item = $cache->getItem('foo');
        $item->set('bar');
        $this->assertSame('foo', $item->getKey());
        $this->assertSame('bar', $item->get());
    }

    public function testBatchGet(): void
    {
        $cache = $this->createCachePool();

        $items = $cache->getItems(['foo1', 'foo2']);
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertArrayHasKey('foo1', $items);
        $this->assertArrayHasKey('foo2', $items);
    }

    protected function createCachePool(): BitrixCacheAdapter
    {
        return new BitrixCacheAdapter();
    }
}
