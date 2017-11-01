<?php

namespace Chemisus\GraphQL;

interface Typer
{
    /**
     * @param Node $node
     * @param $value
     * @return Type
     */
    public function typeOf(Node $node, $value);
}
