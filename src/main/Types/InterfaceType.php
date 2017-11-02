<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Typer;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class InterfaceType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;

    /**
     * @var Field[]
     */
    public $fields = [];

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
        $this->kind = 'INTERFACE';
        $this->name = $name;
    }

    public function addType(Type $type)
    {
        $this->possibleTypes[$type->name()] = $type;
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
        return $this->typeOf($node, $value)->resolve($node, $parent, $value);
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->typer->typeOf($node, $value);
    }

    public function types(Node $node, $values)
    {
        return array_merge([], ...array_map(function ($value) use ($node) {
            return $this->typeOf($node, $value);
        }, $values));
    }

    public function enumValues()
    {
        return null;
    }

    public function interfaces()
    {
        return [];
    }

    public function possibleTypes()
    {
        return array_values($this->possibleTypes);
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
