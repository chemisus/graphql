<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\ObjectType;

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

    /**
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $returnType;

    /**
     * @var string
     */
    private $description;

    /**
     * @var InputValue[]
     */
    private $args;

    /**
     * @var bool
     */
    private $isDeprecated = false;

    /**
     * @var string
     */
    private $deprecationReason;

    /**
     * Field constructor.
     * @param Type $ownerType
     * @param string $name
     * @param Type $returnType
     */
    public function __construct(Type $ownerType, string $name, Type $returnType)
    {
        $this->ownerType = $ownerType;
        $this->name = $name;
        $this->returnType = $returnType;
    }

    public function hasFetcher()
    {
        return $this->fetcher !== null;
    }

    public function setFetcher(Fetcher $fetcher): self
    {
        $this->fetcher = $fetcher;
        return $this;
    }

    public function setResolver(Resolver $resolver): self
    {
        $this->resolver = $resolver;
        return $this;
    }

    public function fetch(Node $node)
    {
        return $this->fetcher ? $this->fetcher->fetch($node) : [];
    }

    public function resolve(Node $node, $parent, $value)
    {
        $value = $this->resolver ? $this->resolver->resolve($node, $parent, $value) : $value;
        return $this->returnType->resolve($node, $parent, $value);
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

    public function description()
    {
        return $this->description;
    }

    /**
     * @return InputValue[]
     */
    public function args()
    {
        return $this->args;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @return string
     */
    public function deprecationReason()
    {
        return $this->deprecationReason;
    }

    public function __toString()
    {
        return sprintf("%s%s: %s", $this->name, "", $this->returnType()->name());
    }
}
