<?php

namespace Chemisus\GraphQL;

interface Wirer
{
    /**
     * @param Document $document
     */
    public function wire(Document $document);
}