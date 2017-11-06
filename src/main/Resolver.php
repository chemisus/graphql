<?php

namespace Chemisus\GraphQL;

interface Resolver
{
    public function resolve(Node $node, $parent, $value);
}