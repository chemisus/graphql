<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Typer;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\FieldsTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;
use Chemisus\GraphQL\Types\Traits\NullEnumValuesTrait;
use Chemisus\GraphQL\Types\Traits\NullInputFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullInterfacesTrait;
use Chemisus\GraphQL\Types\Traits\NullOfTypeTrait;

class InterfaceType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;
    use NullInterfacesTrait;
    use NullInputFieldsTrait;
    use NullEnumValuesTrait;
    use NullOfTypeTrait;
    use FieldsTrait;

    /**
     * @var Typer
     */
    public $typer;

    /**
     * @var Type[]
     */
    private $possibleTypes = [];

    public function __construct(string $name)
    {
        $this->kind = Type::KIND_INTERFACE;
        $this->name = $name;
    }

    public function addType(Type $type)
    {
        $this->possibleTypes[$type->name()] = $type;
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->type($node, $value)->resolve($node, $parent, $value);
    }

    public function type(Node $node, $value): Type
    {
        return $this->typer->typeOf($node, $value);
    }

    public function types(Node $node, $values)
    {
        return array_merge([], ...array_map(function ($value) use ($node) {
            return $this->type($node, $value);
        }, $values));
    }

    public function possibleTypes()
    {
        return array_values($this->possibleTypes);
    }
}
