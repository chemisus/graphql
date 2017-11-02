<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\EnumValue;
use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\KindDoesNotSupportFieldsException;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class EnumType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;

    /**
     * @var EnumValue[]
     */
    private $values;

    public function __construct(string $name)
    {
        $this->kind = 'ENUM';
        $this->name = $name;
    }

    public function fields()
    {
        return null;
    }

    public function addValue(EnumValue $value)
    {
        $this->values[$value->name()] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Field
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name)
    {
        throw new KindDoesNotSupportFieldsException();
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $value;
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
        return $this->values;
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

    public function __toString()
    {
        return sprintf("enum %s {\n}", $this->name);
    }
}
