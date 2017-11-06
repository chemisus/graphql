<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\FragmentDefinitionNode;

class FragmentBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FragmentDefinitionNode $node
         */
        $document->setFragment($builder->buildNode($node->name), new Fragment());
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FragmentDefinitionNode $node
         * @var Fragment $built
         */
        $name = $builder->buildNode($node->name);
        $built = $document->getFragment($name);
        $built->setName($name);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setSelectionSet($builder->buildNode($node->selectionSet));
        $built->setTypeCondition($builder->buildNode($node->typeCondition));
        return $built;
    }
}