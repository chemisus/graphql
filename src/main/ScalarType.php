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
        return null;
    }

    public function types($on = null)
    {
        return [$this];
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->coerce($node, $value);
    }

    public function isList(): bool
    {
        return false;
    }

    public function isNonNull(): bool
    {
        return false;
    }
}