<?php

namespace GraphQL;

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

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name)
    {
        return $this->type->field($name);
    }

    public function resolve(Node $node, $value, callable $resolver = null)
    {
    }
}
