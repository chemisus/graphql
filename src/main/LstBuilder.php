<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\ListValueNode;

class LstBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ListValueNode $node
         */
        return $builder->buildNodes($node->values);
    }
}