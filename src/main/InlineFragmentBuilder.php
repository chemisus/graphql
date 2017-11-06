<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\InlineFragmentNode;

class InlineFragmentBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InlineFragmentNode $node
         */
        $built = new InlineFragment();
        $built->setTypeCondition($builder->buildNode($node->typeCondition));
        $built->setSelectionSet($builder->buildNode($node->selectionSet));
        $built->setDirectives($builder->buildNodes($node->directives));
        return $built;
    }
}