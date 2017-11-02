<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class InputObjectType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;

    /**
     * @var Field[]
     */
    public $fields = [];

    public function __construct(string $name)
    {
        $this->kind = 'INPUT_OBJECT';
        $this->name = $name;
    }

    public function addField(Field $field)
    {
        $this->fields[$field->name()] = $field;
        return $this;
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name)
    {
        return $this->fields[$name];
    }

    public function fields()
    {
        return $this->fields;
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

    public function typeOf(Node $node, $value): Type
    {
        return $this;
    }

    public function types(Node $node, $values)
    {
        return [$this->name];
    }

    public function enumValues()
    {
        return null;
    }

    public function interfaces()
    {
        return null;
    }

    public function possibleTypes()
    {
        return [$this];
    }

    public function inputFields()
    {
        return null;
    }

    public function ofType()
    {
        return null;
    }
}
