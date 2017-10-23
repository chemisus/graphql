<?php

namespace GraphQL\Types;

use GraphQL\Field;
use GraphQL\Node;
use GraphQL\Resolver;
use GraphQL\Typer;

class InterfaceType implements FieldedType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Field[]
     */
    public $fields = [];

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

    public function addField(Field $field)
    {
        $this->fields[$field->name()] = $field;
        return $this;
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name)
    {
        return $this->fields[$name];
    }

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        return $this->typeOf($node, $value)->resolve($node, $parent, $value, $resolver);
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->typer->typeOf($node, $value);
    }
}
