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
        $document->setOperation($builder->buildNode($node->name) ?? 'Query', new Operation());
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var OperationDefinitionNode $node
         * @var Operation $built
         */
        $name = $builder->buildNode($node->name) ?? 'Query';
        $built = $document->getOperation($name);
        $built->setName($name);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setOperation($node->operation);
        $built->setSelectionSet($builder->buildNode($node->selectionSet));
        $node->variableDefinitions;
        return $built;
    }
}