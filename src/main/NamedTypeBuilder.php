<?php

namespace Chemisus\GraphQL;

use Exception;

class NamedTypeBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        $name = $builder->buildNode($node->name);

        if (!array_key_exists($builder->buildNode($node->name), $document->types)) {
            throw new Exception(sprintf("type %s is undefined.", $name));
        }

        return $document->types[$name];
    }
}