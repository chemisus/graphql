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
        return 'SCALAR';
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->coerce($node, $value);
    }
}