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
        return 'INTERFACE';
    }

    public function description()
    {
        return $this->description;
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

    public function fields()
    {
        return $this->fields;
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
        return array_merge([], ...array_map(function ($value) use ($node) {
            return $this->typeOf($node, $value);
        }, $values));
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
