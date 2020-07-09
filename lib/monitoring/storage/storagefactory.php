<?php

namespace Bsi\Queue\Monitoring\Storage;

use Symfony\Component\Messenger\Exception\InvalidArgumentException;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class StorageFactory implements StorageFactoryInterface
{
    private $factories;

    /**
     * @param iterable|StorageFactoryInterface[] $factories
     */
    public function __construct(iterable $factories)
    {
        $this->factories = $factories;
    }

    public function createTransport(string $dsn, array $options): StorageInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn, $options)) {
                return $factory->createTransport($dsn, $options);
            }
        }

        throw new InvalidArgumentException(sprintf('No monitoring storage supports the given Messenger DSN "%s".', $dsn));
    }

    public function supports(string $dsn, array $options): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn, $options)) {
                return true;
            }
        }

        return false;
    }
}
