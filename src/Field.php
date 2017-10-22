<?php

namespace GraphQL;

class Field
{
    /**
     * @var Fetcher
     */
    public $fetcher;

    /**
     * @var Resolver
     */
    public $resolver;

    /**
     * @var ObjectType
     */
    private $ownerType;

    /**mixed
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $returnType;

    /**
     * Field constructor.
     * @param ObjectType $ownerType
     * @param string $name
     * @param Type $returnType
     */
    public function __construct(ObjectType $ownerType, string $name, Type $returnType)
    {
        $this->ownerType = $ownerType;
        $this->name = $name;
        $this->returnType = $returnType;
    }

    public function fetch(Node $node)
    {
        return $this->fetcher ? $this->fetcher->fetch($node) : [];
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->returnType->resolve($node, $parent, $value, $this->resolver);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function ownerType(): ObjectType
    {
        return $this->ownerType;
    }

    public function returnType(): Type
    {
        return $this->returnType;
    }
}
