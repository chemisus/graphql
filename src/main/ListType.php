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

    public function resolve(Node $node, $parent, $value)
    {
        return $value === null ? null : array_map(function ($value) use ($node, $parent) {
            return $this->getType()->resolve($node, $parent, $value);
        }, $value);
    }
}