<?php

namespace GraphQL;

class CallbackCoercer implements Coercer
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param Node $node
     * @param $parent
     * @param $value
     * @return mixed
     */
    public function coerce(Node $node, $parent, $value)
    {
        return call_user_func($this->callback, $node, $parent, $value);
    }
}