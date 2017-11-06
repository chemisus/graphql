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
        $document->setType($builder->buildNode($node->name), new ObjectType());
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ObjectTypeDefinitionNode $node
         * @var ObjectType $built
         */
        $name = $builder->buildNode($node->name);
        $built = $document->getType($name);
        $built->setName($name);
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setInterfaces($builder->buildNodes($node->interfaces));
        $built->setFields($builder->buildNodes($node->fields));
        return $built;
    }
}