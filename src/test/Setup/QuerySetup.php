<?php

namespace GraphQL\Setup;

use GraphQL\Field;
use GraphQL\Node;
use GraphQL\Repositories;
use GraphQL\Schema;
use GraphQL\Types\ListType;
use GraphQL\Utils\CallbackFetcher;
use GraphQL\Utils\CallbackResolver;

class QuerySetup
{
    public function setup(Schema $schema, &$graph)
    {
        $query = $schema->getType('Query');
        $query->addField(new Field($query, 'greeting', $schema->getType('String')));
        $query->addField(new Field($query, 'person', $schema->getType('Person')));
        $query->addField(new Field($query, 'people', new ListType($schema->getType('Person'))));
        $query->addField(new Field($query, 'pets', new ListType($schema->getType('Pet'))));

        $query->field('greeting')->setResolver(new CallbackResolver(function (Node $node) {
            return sprintf("Hello, %s!", $node->arg('name', 'World'));
        }));

        $query->field('person')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $name = $node->arg('name');
                $fetched = Repositories::people()[$name];
                $graph[$name] = $fetched;
                return [$fetched];
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
                return $node->items()[0];
            }));

        $query->field('people')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $fetched = Repositories::people();

                $names = explode(',', $node->arg('names'));
                if (count($names)) {
                    $fetched = $fetched->gets(...$names);
                }

                foreach ($fetched as $item) {
                    $graph[$item->name] = $item;
                }

                return $fetched->toArray();
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
                return $node->items();
            }));

        $query->field('pets')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $fetched = array_merge(
                    [],
                    array_values(repositories::dogs()->toArray()),
                    array_values(repositories::cats()->toArray())
                );

                foreach ($fetched as $pet) {
                    $graph[$pet->name] = $pet;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) use (&$graph) {
                return $node->items();
            }));
    }
}
