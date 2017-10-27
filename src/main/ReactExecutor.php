<?php

namespace GraphQL;

use React\EventLoop\Factory;
use React\Promise\Deferred;

class ReactExecutor
{
    public function execute(Schema $schema, Query $query)
    {
        $root = new Node($schema, $schema->field($query->name()), $query);

        $loop = Factory::create();

        $callback = function (Node $node) use (&$callback) {
            $children = $node->fetch();

            foreach ($children as $child) {
                $deferred = new Deferred();
                $deferred->promise()->then($callback);
                $deferred->resolve($child);
            }
        };

        $deferred = new Deferred();
        $deferred->promise()->then($callback);
        $deferred->resolve($root);

        $loop->run();

        return $root->resolve(null, (object) []);
    }
}
