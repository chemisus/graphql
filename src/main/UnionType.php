<?php

namespace Chemisus\GraphQL;

class UnionType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use TypesTrait;
    use NullFieldTrait;
    use CoercerTrait;
    use TyperTrait;

    public function getKind(): string
    {
        return 'UNION';
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
        return $this->getPossibleTypes();
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