<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\FieldsTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;
use Chemisus\GraphQL\Types\Traits\NullEnumValuesTrait;
use Chemisus\GraphQL\Types\Traits\NullInputFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullInterfacesTrait;
use Chemisus\GraphQL\Types\Traits\NullOfTypeTrait;

class InputObjectType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;
    use NullInterfacesTrait;
    use NullInputFieldsTrait;
    use NullOfTypeTrait;
    use NullEnumValuesTrait;
    use FieldsTrait;

    public function __construct(string $name)
    {
        $this->kind = Type::KIND_INPUT_OBJECT;
        $this->name = $name;
    }

    public function resolve(Node $node, $parent, $value)
    {
        if ($value === null) {
            return null;
        }

        $object = (object) [];

        foreach ($node->children($this->name) as $child) {
            $name = $child->name();
            $field = property_exists($value, $name) ? $value->{$name} : null;
            $object->{$child->alias()} = $child->resolve($value, $field);
        }

        return $object;
    }

    public function type(Node $node, $value): Type
    {
        return $this;
    }

    public function types(Node $node, $values)
    {
        return [$this->name];
    }

    public function possibleTypes()
    {
        return [$this];
    }
}
