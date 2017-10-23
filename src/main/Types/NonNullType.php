<?php

namespace GraphQL\Types;

use GraphQL\Field;
use GraphQL\Node;
use GraphQL\Resolver;

class NonNullType implements Type
{
    /**
     * @var Type
     */
    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function name(): string
    {
        return $this->type->name();
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name)
    {
        return $this->type->field($name);
    }

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        $value = $this->type->resolve($node, $parent, $value, $resolver);

        if ($value === null) {
            throw new \Exception();
        }

        return $value;
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->type->typeOf($node, $value);
    }

    public function types(Node $node, $values)
    {
        return $this->type->types($node, $values);
    }
}
