<?php

namespace Bsi\Queue\Monitoring\Adapter;

use Bsi\Queue\Exception\InvalidArgumentException;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class AdapterFactory implements AdapterFactoryInterface
{
    /** @var iterable|AdapterFactoryInterface[] */
    private $factories;

    /**
     * @param iterable|AdapterFactoryInterface[] $factories
     */
    public function __construct(iterable $factories)
    {
        $this->factories = $factories;
    }

    public function createAdapter(string $name, array $options): AdapterInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($name, $options)) {
                return $factory->createAdapter($name, $options);
            }
        }

        throw new InvalidArgumentException(sprintf('No adapter supports the given name "%s".', $name));
    }

    public function supports(string $name, array $options): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($name, $options)) {
                return true;
            }
        }

        return false;
    }
}
