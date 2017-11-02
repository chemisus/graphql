<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Fetcher;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Resolver;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DeprecationTrait;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class Field
{
    use NameTrait;
    use DescriptionTrait;
    use DeprecationTrait;

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
     * @var Type
     */
    private $returnType;

    /**
     * @var InputValue[]
     */
    private $args;

    /**
     * Field constructor.
     * @param Type $ownerType
     * @param string $name
     * @param Type $returnType
     * @param null|string $description
     */
    public function __construct(Type $ownerType, string $name, Type $returnType, ?string $description = null)
    {
        $this->ownerType = $ownerType;
        $this->name = $name;
        $this->returnType = $returnType;
        $this->description = $description;
    }

    public function ownerType(): ObjectType
    {
        return $this->ownerType;
    }

    public function returnType(): Type
    {
        return $this->returnType;
    }

    /**
     * @return InputValue[]
     */
    public function args(): ?array
    {
        return $this->args;
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

    public function __toString()
    {
        return sprintf("%s%s: %s", $this->name, "", $this->returnType()->name());
    }
}
