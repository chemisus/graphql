<?php

namespace Chemisus\GraphQL;

class EnumType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use ValuesTrait;
    use NullFieldTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return Type::KIND_ENUM;
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
        return $this->getValues();
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
        $values = array_keys($this->getValues());
        $keys = array_map('strtoupper', $values);
        $enums = array_combine($keys, $values);

        return $this->coerce($node, $enums[strtoupper($value)]);
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