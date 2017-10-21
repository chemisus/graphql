<?php

namespace GraphQL;

class Field
{
    /**
     * @var callable
     */
    public $fetcher;

    /**
     * @var callable
     */
    public $resolver;

    /**
     * @var ObjectType
     */
    private $ownerType;

    /**
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
        return is_callable($this->fetcher) ? call_user_func($this->fetcher, $node) : null;
    }

    public function resolve(Node $node)
    {
        return is_callable($this->resolver) ? call_user_func($this->resolver, $node) : null;
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
