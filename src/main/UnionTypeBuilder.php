<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\UnionTypeDefinitionNode;

class UnionTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var UnionTypeDefinitionNode $node
         */
        $document->setType($builder->buildNode($node->name), new UnionType());
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var UnionTypeDefinitionNode $node
         * @var UnionType $built
         */
        $name = $builder->buildNode($node->name);
        $built = $document->getType($name);
        $built->setName($name);
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setTypes($builder->buildNodes($node->types));
        return $built;
    }
}