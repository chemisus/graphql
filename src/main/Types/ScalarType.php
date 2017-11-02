<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Coercer;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;
use Chemisus\GraphQL\Types\Traits\NullEnumValuesTrait;
use Chemisus\GraphQL\Types\Traits\NullFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullInputFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullInterfacesTrait;
use Chemisus\GraphQL\Types\Traits\NullOfTypeTrait;

class ScalarType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;
    use NullFieldsTrait;
    use NullInterfacesTrait;
    use NullInputFieldsTrait;
    use NullEnumValuesTrait;
    use NullOfTypeTrait;

    /**
     * @var Coercer
     */
    private $coercer;

    public function __construct(string $name)
    {
        $this->kind = Type::KIND_SCALAR;
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
        return $this->coercer ? $this->coercer->coerce($node, $value) : $value;
    }

    public function typeOf(Node $node, $value): Type
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

    public function __toString()
    {
        return sprintf("scalar %s", $this->name);
    }
}
