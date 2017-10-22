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
}
