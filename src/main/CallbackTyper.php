<?php

namespace GraphQL;

class CallbackTyper implements Typer
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

    public function type(Node $node, $parent, $value)
    {
        return call_user_func($this->callback, $node, $parent, $value);
    }
}