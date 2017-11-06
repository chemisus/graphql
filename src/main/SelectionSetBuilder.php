<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\SelectionSetNode;

class SelectionSetBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var SelectionSetNode $node
         */
        $built = new SelectionSet();
        $built->setSelections($builder->buildNodes($node->selections));
        return $built;
    }
}