<?php

namespace GraphQL\Types;

use GraphQL\Field;
use GraphQL\Node;
use GraphQL\Resolver;

class ObjectType implements FieldedType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Field[]
     */
    public $fields = [];

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
        $value = $resolver ? $resolver->resolve($node, $parent, $value) : $value;

        if ($value === null) {
            return null;
        }

        $object = (object) [];

        foreach ($node->children($this->name) as $child) {
            $name = $child->name();
            $field = property_exists($value, $name) ? $value->{$name} : null;
            $object->{$child->alias()} = $child->resolve($value, $field);
        }

        return $object;
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this;
    }

    public function types(Node $node, $values)
    {
        return [$this->name];
    }
}
