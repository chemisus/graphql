<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\FieldNode;

class FieldSelectionBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FieldNode $node
         */
        $field = new FieldSelection();
        $field->setName($builder->buildNode($node->name));
        $field->setDirectives($builder->buildNodes($node->directives));
        $field->setSelectionSet($builder->buildNode($node->selectionSet));
        $field->setArguments(array_merge([], ...$builder->buildNodes($node->arguments)));
        $field->setAlias($builder->buildNode($node->alias));
        return $field;
    }
}