<?php

namespace Chemisus\GraphQL;

interface Typer
{
    public function type(Node $node, $value): Type;
}