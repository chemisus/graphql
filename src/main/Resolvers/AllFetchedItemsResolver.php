<?php

namespace Chemisus\GraphQL\Resolvers;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Resolver;

/**
 * A resolver that returns all items fetched by the node.
 *
 * @package Chemisus\GraphQL\Resolvers
 */
class AllFetchedItemsResolver implements Resolver
{
    public function resolve(Node $node, $parent, $value)
    {
        return array_values($node->getItems());
    }
}
