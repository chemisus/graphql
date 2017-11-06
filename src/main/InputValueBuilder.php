<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\InputValueDefinitionNode;

class InputValueBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InputValueDefinitionNode $node
         */
        $built = new InputValue();
        $built->setName($builder->buildNode($node->name));
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setType($builder->buildNode($node->type));
        $built->setDefaultValue($builder->buildNode($node->defaultValue));
        return $built;
    }
}