<?php

namespace Chemisus\GraphQL;

use Exception;

class NonNullType implements Type
{
    use TypeTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return Type::KIND_NON_NULL;
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
        return sprintf("%s!", $this->getType()->getFullName());
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
        return (array)$this->getType()->types($on);
    }

    public function resolve(Node $node, $parent, $value)
    {
        $value = $this->getType()->resolve($node, $parent, $value);

        if ($value === null) {
            throw new Exception(sprintf("%s can not be null", $node->getPath()));
        }

        return $value;
    }

    public function isList(): bool
    {
        return $this->getType()->isList();
    }

    public function isNonNull(): bool
    {
        return true;
    }
}