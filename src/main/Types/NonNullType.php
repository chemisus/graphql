<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Resolver;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;

class NonNullType implements Type
{
    use KindTrait;
    use DescriptionTrait;

    /**
     * @var Type
     */
    private $type;

    public function __construct(Type $type)
    {
        $this->kind = 'NON_NULL';
        $this->type = $type;
    }

    public function name(): string
    {
        return sprintf('%s!', $this->type->name());
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

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        $value = $this->type->resolve($node, $parent, $value);

        if ($value === null) {
            throw new \Exception($node->path() . ' can not be null');
        }

        return $value;
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->type->typeOf($node, $value);
    }

    public function types(Node $node, $values)
    {
        return $this->type->types($node, $values);
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
