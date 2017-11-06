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
         * @var EnumType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setValues($builder->buildNodes($node->values));
        return $type;
    }
}