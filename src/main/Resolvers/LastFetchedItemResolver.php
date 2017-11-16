<?php

namespace Chemisus\GraphQL\Resolvers;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Resolver;

/**
 * A resolver that returns the last item fetched by the node.
 *
 * @package Chemisus\GraphQL\Resolvers
 */
class LastFetchedItemResolver implements Resolver
{
    public function resolve(Node $node, $parent, $value)
    {
        $items = $node->getItems();
        return array_pop($items);
    }
}
