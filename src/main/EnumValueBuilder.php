<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\EnumValueDefinitionNode;

class EnumValueBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var EnumValueDefinitionNode $node
         */
        $built = new EnumValue();
        $built->setName($builder->buildNode($node->name));
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        return $built;
    }
}