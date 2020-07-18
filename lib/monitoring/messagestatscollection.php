<?php

namespace Bsi\Queue\Monitoring;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class MessageStatsCollection implements \Countable, \IteratorAggregate
{
    private $items = [];

    public function add(MessageStats $item): void
    {
        $this->items[] = $item;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
