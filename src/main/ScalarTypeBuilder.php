<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\ScalarTypeDefinitionNode;

class ScalarTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        return $document->setType($builder->buildNode($node->name), new ScalarType());
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ScalarTypeDefinitionNode $node
         * @var ScalarType $built
         */
        $name = $builder->buildNode($node->name);
        $built = $document->getType($name);
        $built->setName($name);
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        return $built;
    }
}