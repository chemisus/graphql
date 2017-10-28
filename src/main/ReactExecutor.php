<?php

namespace GraphQL;

use React\EventLoop\Factory;
use function React\Promise\all;

class ReactExecutor
{
    public function execute(Schema $schema, Query $query)
    {
        $loop = Factory::create();
        Http::init($loop);

        $root = new Node($schema, $schema->field($query->name()), $query);

        $waits = [];
        $a = function ($nodes) use (&$a, &$waits) {
            foreach ($nodes as $node) {
                $waits[] = $node->fetch()->then($a);
            }
        };

        $value = null;
        $root->fetch()->then($a);

        all($waits)->then(function () use ($root, &$value) {
            $value = $root->resolve(null, (object) []);
        });

        $loop->run();

        return $value;
    }
}
