<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\FragmentSpreadNode;

class FragmentSpreadBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FragmentSpreadNode $node
         */
        $built = new FragmentSpread();
        $built->setName($builder->buildNode($node->name));
        $built->setDirectives($node->directives);
        $built->setDocument($document);
        return $built;
    }
}