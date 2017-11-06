<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\InterfaceTypeDefinitionNode;

class InterfaceTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InterfaceTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new InterfaceType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InterfaceTypeDefinitionNode $node
         * @var InterfaceType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setFields($builder->buildNodes($node->fields));
        return $type;
    }
}