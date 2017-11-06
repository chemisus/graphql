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
        return 'ENUM';
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->coerce($node, $value);
    }
}