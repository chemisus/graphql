<?php

namespace Chemisus\GraphQL;

trait TyperTrait
{
    /**
     * @var Typer
     */
    private $typer;

    public function setTyper(Typer $typer)
    {
        $this->typer = $typer;
    }

    public function type(Node $node, $value): Type
    {
        return $this->typer->type($node, $value);
    }
}