<?php

namespace GraphQL\Types;

use GraphQL\Field;
use GraphQL\KindDoesNotSupportFieldsException;
use GraphQL\Node;
use GraphQL\Resolver;

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
     * @return Field[]|null
     */
    public function fields();

    /**
     * @param Node $node
     * @param object $parent
     * @param mixed $value
     * @param Resolver $resolver
     * @return
     */
    public function resolve(Node $node, $parent, $value, Resolver $resolver = null);

    /**
     * @param Node $node
     * @param $value
     * @return Type
     */
    public function typeOf(Node $node, $value): Type;

    /**
     * @param Node $node
     * @param mixed[] $values
     * @return string[]
     */
    public function types(Node $node, $values);
}
