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

        printf("TYPING %s: %s\n", $this->getKind(), $node->getPath());

        $type = $this->typer->type($node, $value);

        printf("TYPED %s to %s\n", $node->getPath(), $type->getName());

        return $type;
    }
}