<?php

namespace GraphQL;

interface Fetcher
{
    /**
     * @param Node $node
     * @return mixed[]
     */
    public function fetch(Node $node);
}
