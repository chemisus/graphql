<?php

namespace GraphQL\Executors;

use GraphQL\Node;
use GraphQL\Query;
use GraphQL\Schema;

class BFSExecutor
{
    public function execute(Schema $schema, Query $query)
    {
        $root = new Node($schema, $schema->field($query->name()), $query);
        $queue = [$root];

        while (count($queue)) {
            $node = array_shift($queue);

            $children = $node->fetch();
            if (count($children)) {
                array_push($queue, ...$children);
            }
        }

        return $root->resolve(null, (object) []);
    }
}
