<?php

namespace GraphQL;

class ScalarType implements Type
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return Field
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name)
    {
        throw new KindDoesNotSupportFieldsException();
    }

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        return $resolver ? $resolver->resolve($node, $parent, $value) : $value;
    }
}
