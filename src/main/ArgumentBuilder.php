<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\ArgumentNode;

class ArgumentBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ArgumentNode $node
         */
        return [$builder->buildNode($node->name) => $builder->buildNode($node->value)];
    }
}