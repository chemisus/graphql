<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Schema;
use React\EventLoop\Factory;
use function React\Promise\all;

class ReactExecutor
{
    public function execute(Schema $schema, Query $query)
    {
        $loop = Factory::create();
        Http::init($loop);

        $root = new Node($schema, $schema->field($query->name()), $query);

        $queue = [];

        /**
         * @param Node[] $nodes
         */
        $fetchChildren = function ($nodes) use (&$fetchChildren, &$queue) {
            foreach ($nodes as $node) {
                $queue[] = $node->fetch()->then($fetchChildren);
            }
        };

        $root->fetch()->then($fetchChildren);

        $value = null;
        all($queue)->then(function () use ($root, &$value) {
            $value = $root->resolve(null, (object) []);
        });

        $loop->run();

        return $value;
    }
}
