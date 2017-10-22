<?php

namespace GraphQL;

class CallbackFetcher implements Fetcher
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function fetch(Node $node)
    {
        return call_user_func($this->callback, $node);
    }
}