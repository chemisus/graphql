<?php

namespace GraphQL;

interface Type
{
    /**
     * @param string $name
     * @return Field
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name);

    /**
     * @param Node $node
     * @param mixed $value
     * @param callable|null $resolver
     * @return
     */
    public function resolve(Node $node, $value, callable $resolver = null);
}
