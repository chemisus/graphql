<?php

namespace Chemisus\GraphQL;

use ArrayAccess;
use IteratorAggregate;

class Repository implements ArrayAccess, IteratorAggregate
{
    private $records;

    public function __construct(iterable $records = [])
    {
        $this->putAll($records);
    }

    public function getIterator()
    {
        foreach ($this->records as $key => $record) {
            yield $key => $record;
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->records);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->put($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->records[$offset]);
    }

    public function get($id)
    {
        return $this->offsetExists($id) ? $this->records[$id] : null;
    }

    public function gets(...$ids)
    {
        return new Repository(array_map([$this, 'get'], $ids));
    }

    public function put($id, $records)
    {
        foreach ($records as $record) {
            $this->records[$id] = $record;
        }
    }

    public function putAll($records)
    {
        foreach ($records as $id => $record) {
            $this->records[$id] = $record;
        }
    }

    public function map(callable $callback)
    {
        return new Repository(array_map($callback, $this->records));
    }

    public function filter(callable $callback)
    {
        return new Repository(array_filter($this->records, $callback));
    }

    public function toArray()
    {
        return $this->records;
    }
}
