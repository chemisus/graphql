<?php

namespace GraphQL;

use GraphQL\Types\Type;

interface Typer
{
    /**
     * @param Node $node
     * @param $value
     * @return Type
     */
    public function typeOf(Node $node, $value);
}
