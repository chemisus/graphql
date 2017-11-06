<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\SchemaDefinitionNode;

class SchemaBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var SchemaDefinitionNode $node
         */
        $built = $document->getSchema();
        $built->setOperationTypes($builder->buildNodes($node->operationTypes));
        return $built;
    }
}