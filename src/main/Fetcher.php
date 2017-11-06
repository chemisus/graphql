<?php

namespace Chemisus\GraphQL;

interface Fetcher
{
    public function fetch(Node $node, $parents);
}