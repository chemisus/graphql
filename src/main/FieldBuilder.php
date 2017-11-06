<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\FieldDefinitionNode;

class FieldBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FieldDefinitionNode $node
         */
        $built = new Field();
        $built->setName($builder->buildNode($node->name));
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setArguments($builder->buildNodes($node->arguments));
        $built->setType($builder->buildNode($node->type));
        return $built;
    }
}