<?php

namespace Chemisus\GraphQL;

class ListType implements Type
{
    use TypeTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return Type::KIND_LIST;
    }

    public function getName(): ?string
    {
        return null;
    }

    public function getBaseName(): string
    {
        return $this->getType()->getBaseName();
    }

    public function getFullName(): string
    {
        return sprintf("[%s]", $this->getType()->getFullName());
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getField(string $name): Field
    {
        return $this->getType()->getField($name);
    }

    public function getFields()
    {
        return $this->getType()->getFields();
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
        return $this->getType();
    }

    public function types($on = null)
    {
        return $this->getType()->types($on);
    }

    public function resolve(Node $node, $parent, $value)
    {
        printf("RESOLVING %s: %s\n", $this->getKind(), $node->getPath());

        return $value === null ? null : array_map(function ($value) use ($node, $parent) {
            return $this->getType()->resolve($node, $parent, $value);
        }, $value);
    }
}