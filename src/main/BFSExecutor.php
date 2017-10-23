<?php

namespace GraphQL;

class BFSExecutor
{
    public function execute(Schema $schema, Query $query)
    {
        $root = new Node($schema, $schema->field($query->name()), $query);
        $nodes = [];
        $queue = [$root];

        while (count($queue)) {
            $node = array_shift($queue);
            array_push($nodes, $node);
            $children = $node->fetch();
            if (count($children)) {
                array_push($queue, ...$children);
            }
        }

        return $root->resolve(null, (object) []);
    }
}