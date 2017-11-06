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
         * @var InputObjectType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setFields($builder->buildNodes($node->fields));
        return $type;
    }
}