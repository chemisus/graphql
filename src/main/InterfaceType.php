<?php

namespace Chemisus\GraphQL;

class InterfaceType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use FieldsTrait;
    use TypesTrait;
    use CoercerTrait;
    use TyperTrait;

    public function getKind(): string
    {
        return Type::KIND_INTERFACE;
    }

    public function getInterfaces()
    {
    }

    public function getPossibleTypes()
    {
        return $this->getTypes();
    }

    public function getEnumValues()
    {
    }

    public function getOfType(): ?Type
    {
        return null;
    }

    public function types($on = null)
    {
        return $this->getTypes();
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->type($node, $value)->resolve($node, $parent, $value);
    }

    public function isList(): bool
    {
        return false;
    }
}