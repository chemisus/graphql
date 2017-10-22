<?php

namespace GraphQL;

class BFSExecutor
{
    public function execute(ObjectType $schemaType, Query $query)
    {
        $root = new Node($schemaType->field($query->name()), $query);
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