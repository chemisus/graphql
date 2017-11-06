<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;

class InputObjectTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InputObjectTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new InputObjectType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InputObjectTypeDefinitionNode $node
         * @var InputObjectType $built
         */
        $name = $builder->buildNode($node->name);
        $built = $document->types[$name];
        $built->setName($name);
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setFields($builder->buildNodes($node->fields));
        return $built;
    }
}