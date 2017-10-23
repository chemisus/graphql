<?php

namespace GraphQL\Utils;

use GraphQL\Node;
use GraphQL\Typer;

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

    public function typeOf(Node $node, $value)
    {
        return call_user_func($this->callback, $node, $value);
    }
}