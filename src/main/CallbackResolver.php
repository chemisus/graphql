<?php

namespace GraphQL;

class CallbackResolver implements Resolver
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

    public function resolve(Node $node, $owner, $value = null)
    {
        return call_user_func($this->callback, $node, $owner, $value);
    }
}