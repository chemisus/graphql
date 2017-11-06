<?php

namespace Chemisus\GraphQL;

use Exception;
use GraphQL\Language\AST\NonNullTypeNode;

class NonNullBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var NonNullTypeNode $node
         */
        $built = new NonNullType();
        $type = $builder->buildNode($node->type);
        if ($type === null) {
            var_dump($node->type);
        }
        $built->setType($type);
        return $built;
    }
}