<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\EnumTypeDefinitionNode;

class EnumTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var EnumTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new EnumType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var EnumTypeDefinitionNode $node
         * @var EnumType $built
         */
        $name = $builder->buildNode($node->name);
        $built = $document->types[$name];
        $built->setName($name);
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setValues($builder->buildNodes($node->values));
        return $built;
    }
}