<?php

namespace Chemisus\GraphQL;

class ScalarTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        return $document->types[$builder->buildNode($node->name)] = new ScalarType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ScalarType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        return $type;
    }
}