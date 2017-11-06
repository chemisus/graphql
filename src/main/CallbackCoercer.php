<?php

namespace Chemisus\GraphQL;

class CallbackCoercer implements Coercer
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

    public function coerce(Node $node, $value)
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}