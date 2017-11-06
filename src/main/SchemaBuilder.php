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
        $document->schema = $built = new Schema();
        $built->setOperationTypes($builder->buildNodes($node->operationTypes));
        return $built;
    }
}