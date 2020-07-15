<?php

namespace Bsi\Queue\Monitoring;

class ChartDataSet implements \ArrayAccess, \Countable
{
    private $values = [];

    public function addValue(int $timestamp, $value): void
    {
        $this->values[$timestamp] = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->values[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException(
                sprintf(
                    'No value at position "%s"',
                    $offset
                )
            );
        }

        return $this->values[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException('Please use addValue() instead.');
    }

    public function offsetUnset($offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException(
                sprintf(
                    'No value at position "%s"',
                    $offset
                )
            );
        }

        unset($this->values[$offset]);
    }

    public function count(): int
    {
        return count($this->values);
    }
}
