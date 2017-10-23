<?php

namespace GraphQL\Types;

use GraphQL\Field;
use GraphQL\KindDoesNotSupportFieldsException;
use GraphQL\Node;
use GraphQL\Resolver;

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

    public function name(): string
    {
        return $this->name;
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

    public function typeOf(Node $node, $value): Type
    {
        return $this;
    }
}
