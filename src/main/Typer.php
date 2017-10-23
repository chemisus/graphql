<?php

namespace GraphQL;

interface Typer
{
    /**
     * @param Node $node
     * @return Type
     */
    public function type(Node $node);
}
