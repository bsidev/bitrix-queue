<?php

declare(strict_types=1);

namespace Bsi\Queue\Tests\Fixtures;

use Bsi\Queue\Cache\BitrixCacheInterface;

class DummyCache implements BitrixCacheInterface
{
    protected array $vars = [];

    public function forceRewriting(bool $value): void
    {
    }

    public function cleanDir($initDir = false, $baseDir = 'cache'): void
    {
        $this->vars = [];
    }

    public function initCache($ttl, $uniqueString, $initDir = false, $baseDir = 'cache'): bool
    {
        return true;
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public function startDataCache(?int $ttl = null, ?string $uniqueString = null, ?string $initDir = null, array $vars = [], ?string $baseDir = null): bool
    {
        $this->vars[$uniqueString] = $vars;
        return true;
    }

    public function endDataCache(?array $vars = null): void
    {
    }

    public function clean(string $uniqueString, ?string $initDir = null, ?string $baseDir = null): void
    {
        $this->cleanDir();
    }
}
