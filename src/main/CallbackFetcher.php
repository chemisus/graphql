<?php

namespace Chemisus\GraphQL;

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

    public function fetch(Node $node, $parents)
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}