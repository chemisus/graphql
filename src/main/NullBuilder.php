<?php

namespace Chemisus\GraphQL;

class NullBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        return null;
    }
}