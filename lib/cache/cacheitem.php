<?php

namespace Bsi\Queue\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
final class CacheItem implements CacheItemInterface
{
    protected $key;
    protected $value;
    protected $isHit = false;

    public function __construct(string $key, $value, bool $isHit = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit(): bool
    {
        return $this->isHit;
    }

    /**
     * {@inheritdoc}
     */
    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration): self
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time): self
    {
        return $this;
    }
}
