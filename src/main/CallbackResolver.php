<?php

namespace Chemisus\GraphQL;

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

    public function resolve(Node $node, $parent, $value)
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}