<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\TypeExtensionDefinitionNode;

class ExtendTypeBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var TypeExtensionDefinitionNode $node
         * @var ObjectType $built
         */
        $name = $builder->buildNode($node->definition->name);
        $built = $document->getType($name);
        $built->putFields($builder->buildNodes($node->definition->fields));
        return $built;
    }
}