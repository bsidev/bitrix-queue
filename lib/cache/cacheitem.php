<?php

namespace Bsi\Queue\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
final class CacheItem implements CacheItemInterface
{
    protected string $key;
    protected mixed $value;
    protected bool $isHit = false;

    public function __construct(string $key, mixed $value, bool $isHit = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): mixed
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
    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter(int|\DateInterval|null $time): static
    {
        return $this;
    }
}
