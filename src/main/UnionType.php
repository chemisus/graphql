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

    /**
     * @var string
     */
    private $description;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function kind()
    {
        return 'UNION';
    }

    public function description()
    {
        return $this->description;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function field(string $name)
    {
        throw new KindDoesNotSupportFieldsException();
    }

    public function fields()
    {
        return null;
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

    public function enumValues()
    {
        return null;
    }

    public function interfaces()
    {
        return null;
    }

    public function possibleTypes()
    {
        return null;
    }

    public function inputFields()
    {
        return null;
    }

    public function ofType()
    {
        return null;
    }
}
