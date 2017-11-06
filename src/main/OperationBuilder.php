<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\OperationDefinitionNode;

class OperationBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var OperationDefinitionNode $node
         */
        $document->operations[$builder->buildNode($node->name) ?? 'Query'] = new Operation();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var OperationDefinitionNode $node
         * @var Operation $operation
         */
        $name = $builder->buildNode($node->name) ?? 'Query';
        $operation = $document->operations[$name];
        $operation->setName($name);
        $operation->setDirectives($node->directives);
        $operation->setOperation($node->operation);
        $operation->setSelectionSet($builder->buildNode($node->selectionSet));
        $node->variableDefinitions;
        return $operation;
    }
}