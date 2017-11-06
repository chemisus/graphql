<?php

namespace Chemisus\GraphQL;

interface Coercer
{
    public function coerce(Node $node, $value);
}