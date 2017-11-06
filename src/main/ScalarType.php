<?php

namespace Chemisus\GraphQL;

class ScalarType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use NullFieldTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return Type::KIND_SCALAR;
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
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->coerce($node, $value);
    }
}