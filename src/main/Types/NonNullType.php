<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Resolver;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\KindTrait;
use Chemisus\GraphQL\Types\Traits\NullEnumValuesTrait;
use Chemisus\GraphQL\Types\Traits\NullInputFieldsTrait;
use Chemisus\GraphQL\Types\Traits\NullInterfacesTrait;

class NonNullType implements Type
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
        $this->kind = Type::KIND_NON_NULL;
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

    public function possibleTypes()
    {
        return $this->type->possibleTypes();
    }

    public function ofType()
    {
        return $this->type;
    }
}
