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
        $document->types[$builder->buildNode($node->name)] = new UnionType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var UnionTypeDefinitionNode $node
         * @var UnionType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setTypes($builder->buildNodes($node->types));
        return $type;
    }
}