<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Coercer;
use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\FieldsTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
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

    /**
     * @var Coercer
     */
    private $coercer;

    public function __construct(string $name)
    {
        $this->kind = Type::KIND_OBJECT;
        $this->name = $name;
    }

    /**
     * @param Coercer $coercer
     */
    public function setCoercer(Coercer $coercer)
    {
        $this->coercer = $coercer;
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

    public function type(Node $node, $value): Type
    {
        return $this;
    }

    public function interfaces()
    {
        return null;
    }

    public function possibleTypes()
    {
        return [$this];
    }

    public function __toString()
    {
        return sprintf("type %s {\n    %s\n}", $this->name, join("\n    ", array_filter($this->fields, function (Field $field) {
            return !preg_match('/^__/', $field->name());
        })));
    }
}
