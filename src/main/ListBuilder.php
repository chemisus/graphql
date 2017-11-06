<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\ListTypeNode;

class ListBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ListTypeNode $node
         */
        $built = new ListType();
        $built->setType($builder->buildNode($node->type));
        return $built;
    }
}