<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\LeafTypeTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;
use Chemisus\GraphQL\Types\Traits\NullFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullInputFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullInterfacesTrait;
use Chemisus\GraphQL\Types\Traits\NullOfTypeTrait;

class EnumType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;
    use NullFieldsTrait;
    use NullInterfacesTrait;
    use NullInputFieldsTrait;
    use NullOfTypeTrait;
    use LeafTypeTrait;

    /**
     * @var EnumValue[]
     */
    private $values;

    public function __construct(string $name)
    {
        $this->kind = Type::KIND_ENUM;
        $this->name = $name;
    }

    public function addValue(EnumValue $value)
    {
        $this->values[$value->name()] = $value;
        return $this;
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $value;
    }

    public function enumValues(): ?array
    {
        return $this->values;
    }

    public function __toString()
    {
        return sprintf("enum %s {\n}", $this->name);
    }
}
