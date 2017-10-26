<?php

namespace GraphQL;

interface Coercer
{
    /**
     * @param Node $node
     * @param mixed $value
     * @return mixed
     */
    public function coerce(Node $node, $value);
}