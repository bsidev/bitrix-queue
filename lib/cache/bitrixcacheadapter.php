<?php

namespace Bsi\Queue\Cache;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixCacheAdapter implements CacheItemPoolInterface
{
    /** @var int */
    private $lifetime;
    /** @var string */
    private $dir;
    /** @var Cache */
    private $cache;
    /** @var array */
    private $values = [];

    public function __construct(int $lifetime = 0, string $dir = '/bsi/queue')
    {
        $this->lifetime = $lifetime > 0 ? $lifetime : 31536000;
        $this->dir = $dir;
        /** @noinspection NullPointerExceptionInspection */
        $this->cache = Application::getInstance()->getCache();
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key): CacheItemInterface
    {
        return current($this->getItems([$key]));
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []): array
    {
        $items = [];

        $fetched = $this->doFetch($keys);
        foreach ($fetched as $id => $value) {
            if (!isset($keys[$id])) {
                $id = key($keys);
            }
            $key = $keys[$id];
            unset($keys[$id]);
            $items[$key] = new CacheItem($key, $value, true);
        }

        foreach ($keys as $key) {
            $items[$key] = new CacheItem($key, null, false);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $this->values[$item->getKey()] = $item->get();

        return $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->cache->cleanDir($this->dir);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key): bool
    {
        return $this->deleteItems([$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->cache->clean($key, $this->dir);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        $this->cache->forceRewriting(true);
        foreach ($this->values as $key => $value) {
            $this->cache->startDataCache($this->lifetime, $key, $this->dir, $value);
            $this->cache->endDataCache();
        }
        $this->cache->forceRewriting(false);

        return true;
    }

    private function doFetch(array $ids): array
    {
        $values = [];

        foreach ($ids as $id) {
            if ($this->cache->initCache($this->lifetime, $id, $this->dir)) {
                $values[$id] = $this->cache->getVars();
            }
        }

        return $values;
    }
}
