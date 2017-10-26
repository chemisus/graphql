<?php

namespace GraphQL;

class BFSExecutor
{
    public function execute(Schema $schema, Query $query)
    {
        $root = new Node($schema, $schema->field($query->name()), $query);

        /**
         * @var Node[] $queue
         */
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
