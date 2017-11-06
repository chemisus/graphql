<?php

namespace Chemisus\GraphQL;

class ValueBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        return $node->value;
    }
}