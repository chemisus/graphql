<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Coercer;
use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\KindDoesNotSupportFieldsException;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class ScalarType implements Type
{
    use KindTrait;
    use NameTrait;
    use DescriptionTrait;

    /**
     * @var Coercer
     */
    private $coercer;

    public function __construct(string $name)
    {
        $this->kind = 'SCALAR';
        $this->name = $name;
    }

    /**
     * @param Coercer $coercer
     */
    public function setCoercer(Coercer $coercer)
    {
        $this->coercer = $coercer;
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

    public function fields()
    {
        return null;
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

    public function __toString()
    {
        return sprintf("scalar %s", $this->name);
    }
}
