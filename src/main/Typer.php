<?php

namespace GraphQL;

interface Typer
{
    /**
     * @param Node $node
     * @param $value
     * @return Type
     */
    public function type(Node $node, $value);
}
