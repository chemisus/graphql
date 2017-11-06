<?php

namespace Chemisus\GraphQL;

class InputObjectType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use FieldsTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return 'INPUT_OBJECT';
    }

    public function getField(string $name): Field
    {
    }

    public function resolve(Node $node, $parent, $value)
    {
    }
}