<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\CoercerTrait;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\FieldsTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\LeafTypeTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;
use Chemisus\GraphQL\Types\Traits\NullEnumValuesTrait;
use Chemisus\GraphQL\Types\Traits\NullInputFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullOfTypeTrait;

class ObjectType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;
    use NullInputFieldsTrait;
    use NullEnumValuesTrait;
    use NullOfTypeTrait;
    use FieldsTrait;
    use LeafTypeTrait;
    use CoercerTrait;

    /**
     * @var Type[]
     */
    private $interfaces = [];

    public function __construct(string $name)
    {
        $this->kind = Type::KIND_OBJECT;
        $this->name = $name;
    }

    public function interfaces()
    {
        return array_values($this->interfaces);
    }

    public function addInterface(Type $type)
    {
        $this->interfaces[$type->name()] = $type;
    }

    public function resolve(Node $node, $parent, $value)
    {
        if ($value === null) {
            return null;
        }

        $coerced = $this->coercer ? $this->coercer->coerce($node, $value) : (object) [];
        $object = (object) [];

        foreach ($node->children($this->name) as $child) {
            $name = $child->name();
            $field = isset($coerced->{$name}) ? $coerced->{$name} : (isset($value->{$name}) ? $value->{$name} : null);
            $object->{$child->alias()} = $child->resolve($value, $field);
        }

        return $object;
    }

    public function __toString()
    {
        return sprintf("type %s {\n    %s\n}", $this->name, join("\n    ", array_filter($this->fields, function (Field $field) {
            return !preg_match('/^__/', $field->name());
        })));
    }
}
