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

    public function getField(string $name): Field
    {
    }

    public function getInterfaces()
    {
    }

    public function getPossibleTypes()
    {
    }

    public function getEnumValues()
    {
    }

    public function getOfType(): ?Type
    {
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->type($node, $value)->resolve($node, $parent, $value);
    }
}