<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NullEnumValuesTrait;
use Chemisus\GraphQL\Types\Traits\NullInputFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullInterfacesTrait;

class ListType implements Type
{
    use KindTrait;
    use DescriptionTrait;
    use NullInterfacesTrait;
    use NullInputFieldsTrait;
    use NullEnumValuesTrait;

    /**
     * @var Type
     */
    private $type;

    public function __construct(Type $type)
    {
        $this->kind = Type::KIND_LIST;
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

    public function type(Node $node, $value): Type
    {
        return $this->type->type($node, $value);
    }

    public function types(Node $node, $values)
    {
        return array_map(function ($value) use ($node) {
            return $this->type($node, $value)->name();
        }, $values);
    }

    public function possibleTypes()
    {
        return $this->type->possibleTypes();
    }

    public function ofType()
    {
        return $this->type;
    }
}
