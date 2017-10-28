<?php

namespace GraphQL\Setup;

use GraphQL\CallbackFetcher;
use GraphQL\CallbackResolver;
use GraphQL\Field;
use GraphQL\Http;
use GraphQL\Node;
use GraphQL\Schema;

class ReactSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $react = $schema->getType('React');
        $react->addField(new Field($react, 'message', $schema->getType('String')));

        $react->field('message')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $duration = $node->arg('duration', 0);
                $size = $node->arg('size', 10);
                return [
                    Http::get("https://httpbin.org/drip?numbytes={$size}&duration={$duration}&delay=0&code=200")
                        ->then(function ($data) use (&$graph, $node, $size) {
                            $graph['react-' . $size] = $data;
                            return $data;
                        })
                ];
            }))
            ->setResolver(new CallbackResolver(function (Node $node) use (&$graph) {
                $size = $node->arg('size', 10);
                return $graph['react-' . $size];
            }));
    }
}