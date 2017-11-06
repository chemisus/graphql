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
        $document->setType($builder->buildNode($node->name), new InterfaceType());
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InterfaceTypeDefinitionNode $node
         * @var InterfaceType $built
         */
        $name = $builder->buildNode($node->name);
        $built = $document->getType($name);
        $built->setName($name);
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setFields($builder->buildNodes($node->fields));
        return $built;
    }
}