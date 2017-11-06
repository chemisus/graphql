<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\OperationTypeDefinitionNode;

class OperationTypeBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var OperationTypeDefinitionNode $node
         */
        $built = new OperationType();
        $built->setOperation($node->operation);
        $built->setType($builder->buildNode($node->type));
        return $built;
    }
}