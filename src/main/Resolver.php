<?php

namespace GraphQL;

interface Resolver
{
    /**
     * @param Node $node
     * @param object $owner
     * @param mixed $value
     * @return mixed
     */
    public function resolve(Node $node, $owner, $value = null);
}
