<?php

namespace GraphQL;

interface Typer
{
    /**
     * @param Node $node
     * @param $parent
     * @param $value
     * @return Type
     */
    public function type(Node $node, $parent, $value);
}
