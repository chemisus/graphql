<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Typer;

trait CompositeTypeTrait
{
    /**
     * @var Type[]
     */
    private $possibleTypes = [];

    /**
     * @var Typer
     */
    public $typer;

    public function setTyper(Typer $typer)
    {
        $this->typer = $typer;
    }

    public function addType(Type $type)
    {
        $this->possibleTypes[$type->name()] = $type;
    }

    public function type(Node $node, $value): Type
    {
        return $this->typer->type($node, $value);
    }

    public function possibleTypes(): array
    {
        return array_values($this->possibleTypes);
    }
}
