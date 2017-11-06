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
        $document->fragments[$builder->buildNode($node->name)] = new Fragment();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FragmentDefinitionNode $node
         * @var Fragment $fragment
         */
        $fragment = $document->fragments[$builder->buildNode($node->name)];
        $fragment->setName($builder->buildNode($node->name));
        $fragment->setDirectives($builder->buildNodes($node->directives));
        $fragment->setSelectionSet($builder->buildNode($node->selectionSet));
        $fragment->setTypeCondition($builder->buildNode($node->typeCondition));
        return $fragment;
    }
}