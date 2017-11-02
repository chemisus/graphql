<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;

class ListType implements Type
{
    use KindTrait;
    use DescriptionTrait;

    /**
     * @var Type
     */
    private $type;

    public function __construct(Type $type)
    {
        $this->kind = 'LIST';
        $this->type = $type;
    }

    public function name(): string
    {
        return sprintf('[%s]', $this->type->name());
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name)
    {
        return $this->type->field($name);
    }

    public function fields()
    {
        return $this->type->fields();
    }

    public function resolve(Node $node, $parent, $value)
    {
        if ($value === null) {
            return null;
        }

        $array = [];

        foreach ($value as $item) {
            $array[] = $this->type->resolve($node, $parent, $item);
        }

        return $array;
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->type->typeOf($node, $value);
    }

    public function types(Node $node, $values)
    {
        return array_map(function ($value) use ($node) {
            return $this->typeOf($node, $value)->name();
        }, $values);
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
        return $this->type->possibleTypes();
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
