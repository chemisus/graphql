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

    public function resolve(Node $node, $value, callable $resolver = null)
    {
        $value = is_callable($resolver) ? call_user_func($resolver, $node, $value) : $value;

        if ($value === null) {
            return null;
        }

        $object = (object) [];

        foreach ($node->children() as $child) {
            $name = $child->name();
            $object->{$child->alias()} = $child->resolve(property_exists($value, $name) ? $value->{$name} : null);
        }

        return $object;
    }
}
