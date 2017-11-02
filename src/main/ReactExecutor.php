<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Schema;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use function React\Promise\all;

class ReactExecutor
{
    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(LoopInterface $loop = null)
    {
        $this->loop = $loop ?? Factory::create();
        Http::init($this->loop);
    }

    public function execute(Schema $schema, Query $query)
    {
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

        $this->loop->run();

        return $value;
    }
}
