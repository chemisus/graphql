<?php

namespace Chemisus\GraphQL\Resolvers;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Resolver;

/**
 * A resolver that returns the first item fetched by the node.
 *
 * @package Chemisus\GraphQL\Resolvers
 */
class FirstFetchedItemResolver implements Resolver
{
    public function resolve(Node $node, $parent, $value)
    {
        $items = $node->getItems();
        return array_shift($items);
    }
}
