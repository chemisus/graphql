<?php

namespace Chemisus\GraphQL;

class InputObjectType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use FieldsTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return Type::KIND_INPUT_OBJECT;
    }

    public function getField(string $name): Field
    {
    }

    public function setTyper(Typer $typer)
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
        return null;
    }

    public function resolve(Node $node, $parent, $value)
    {
    }
}