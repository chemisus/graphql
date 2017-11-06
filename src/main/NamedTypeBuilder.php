<?php

namespace Chemisus\GraphQL;

use Exception;

class NamedTypeBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        $name = $builder->buildNode($node->name);

        if (!$document->hasType($name)) {
            throw new Exception(sprintf("type %s is undefined.", $name));
        }

        return $document->getType($name);
    }
}