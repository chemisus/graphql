<?php

namespace Chemisus\GraphQL;

use Exception;

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
        if (!$this->typer) {
            throw new Exception(sprintf("%s needs as typer.", $this->getName()));
        }

        return $this->typer->type($node, $value);
    }
}