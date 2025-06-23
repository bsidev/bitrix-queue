<?php

namespace Bsi\Queue\Cache;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;

class BitrixCache implements BitrixCacheInterface
{
    private Cache $cache;

    public function __construct()
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->cache = Application::getInstance()->getCache();
    }

    public function forceRewriting(bool $value): void
    {
        $this->cache->forceRewriting($value);
    }

    public function cleanDir(?string $initDir = null, ?string $baseDir = null): void
    {
        $this->cache->cleanDir(
            $initDir ?? false,
            $baseDir ?? 'cache'
        );
    }

    public function initCache(int $ttl, string $uniqueString, ?string $initDir = null, ?string $baseDir = null): bool
    {
        return $this->cache->initCache(
            $ttl,
            $uniqueString,
            $initDir ?? false,
            $baseDir ?? 'cache'
        ) ?? false;
    }

    public function getVars(): array
    {
        $vars = $this->cache->getVars();
        return is_array($vars) ? $vars : [];
    }

    public function startDataCache(?int $ttl = null, ?string $uniqueString = null, ?string $initDir = null, array $vars = [], ?string $baseDir = null): bool
    {
        return $this->cache->startDataCache(
            $ttl ?? false,
            $uniqueString ?? false,
            $initDir ?? false,
            $vars,
            $baseDir ?? 'cache'
        ) ?? false;
    }

    public function endDataCache(?array $vars = null): void
    {
        $this->cache->endDataCache($vars ?? false);
    }

    public function clean(string $uniqueString, ?string $initDir = null, ?string $baseDir = null): void
    {
        $this->cache->clean(
            $uniqueString,
            $initDir ?? false,
            $baseDir ?? 'cache'
        );
    }
}
