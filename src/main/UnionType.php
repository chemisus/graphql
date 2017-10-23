<?php

namespace GraphQL;

class UnionType implements FieldedType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Typer
     */
    public $typer;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        return $this->typeOf($node, $value)->resolve($node, $parent, $value, $resolver);
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->typer->type($node, $value);
    }
}
