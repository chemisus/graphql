<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\Variable;

class VariableBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var Variable $node
         */
        return $document->getVariable($builder->buildNode($node->name));
    }
}