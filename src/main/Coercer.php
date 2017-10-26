<?php

namespace GraphQL;

interface Coercer
{
    /**
     * @param Node $node
     * @param mixed $parent
     * @param mixed $value
     * @return mixed
     */
    public function coerce(Node $node, $parent, $value);
}