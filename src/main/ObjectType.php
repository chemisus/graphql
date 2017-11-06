<?php

namespace Chemisus\GraphQL;

class ObjectType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use FieldsTrait;
    use InterfacesTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return Type::KIND_OBJECT;
    }

    public function setTyper(Typer $typer)
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
        if ($value === null) {
            return null;
        }

        $coerced = $this->coerce($node, $value) ?? (object) [];

        $object = (object) [];

        foreach ($node->getChildren() as $child) {
            $name = $child->getField()->getName();
            $field = isset($coerced->{$name}) ? $coerced->{$name} : (isset($value->{$name}) ? $value->{$name} : null);
            $object->{$child->getSelection()->getAlias()} = $child->resolve($value, $field);
        }

        return $object;
    }
}