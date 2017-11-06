<?php

namespace Chemisus\GraphQL;

interface Factory
{
    public function make(DocumentBuilder $builder, Document $document, $node);
}