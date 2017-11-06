<?php

namespace Chemisus\GraphQL;

interface Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node);
}