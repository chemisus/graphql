<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\ObjectTypeDefinitionNode;

class ObjectTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ObjectTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new ObjectType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ObjectTypeDefinitionNode $node
         * @var ObjectType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setInterfaces($builder->buildNodes($node->interfaces));
        $type->setFields($builder->buildNodes($node->fields));
        return $type;
    }
}