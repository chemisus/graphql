<?php

namespace GraphQL;

interface Type
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param string $name
     * @return Field
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name);

    /**
     * @param Node $node
     * @param object $parent
     * @param mixed $value
     * @param Resolver $resolver
     * @return
     */
    public function resolve(Node $node, $parent, $value, Resolver $resolver = null);
}
