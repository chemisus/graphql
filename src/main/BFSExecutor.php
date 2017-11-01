<?php

namespace Chemisus\GraphQL;

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

            $node->fetch()->then(function ($children) use (&$queue) {
                if (count($children)) {
                    array_push($queue, ...$children);
                }
            });
        }

        return $root->resolve(null, (object) []);
    }
}
