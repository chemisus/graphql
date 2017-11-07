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
        return array_merge([], ...array_map(function (Type $type) use ($on) {
            return $type->types($on);
        }, $this->getTypes()));
    }

    public function resolve(Node $node, $parent, $value)
    {
        printf("RESOLVING %s: %s\n", $this->getKind(), $node->getPath());

        return $this->type($node, $value)->resolve($node, $parent, $value);
    }
}