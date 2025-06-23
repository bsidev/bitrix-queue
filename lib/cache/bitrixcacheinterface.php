<?php

namespace Bsi\Queue\Cache;

interface BitrixCacheInterface
{
    public function forceRewriting(bool $value): void;
    public function cleanDir(?string $initDir = null, ?string $baseDir = null): void;
    public function initCache(int $ttl, string $uniqueString, ?string $initDir = null, ?string $baseDir = null): bool;
    public function getVars(): array;
    public function startDataCache(?int $ttl = null, ?string $uniqueString = null, ?string $initDir = null, array $vars = [], ?string $baseDir = null): bool;
    public function endDataCache(?array $vars = null): void;
    public function clean(string $uniqueString, ?string $initDir = null, ?string $baseDir = null): void;
}
