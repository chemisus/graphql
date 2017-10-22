<?php

namespace GraphQL;

class ObjectType implements Type
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

        foreach ($node->children() as $child) {
            $name = $child->name();
            $object->{$child->alias()} = $child->resolve($value, property_exists($value, $name) ? $value->{$name} : null);
        }

        return $object;
    }
}
