<?php

namespace GraphQL\Types;

use GraphQL\KindDoesNotSupportFieldsException;
use GraphQL\Node;
use GraphQL\Resolver;
use GraphQL\Typer;

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

    public function field(string $name)
    {
        throw new KindDoesNotSupportFieldsException();
    }

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        return $this->typeOf($node, $value)->resolve($node, $parent, $value, $resolver);
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->typer->typeOf($node, $value);
    }

    public function types(Node $node, $values)
    {
        return array_map(function ($value) use ($node) {
            return $this->typeOf($node, $value)->name();
        }, $values);
    }
}
