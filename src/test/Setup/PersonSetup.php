<?php

namespace Chemisus\GraphQL\Setup;

use Chemisus\GraphQL\CallbackFetcher;
use Chemisus\GraphQL\CallbackResolver;
use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\ListType;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\NonNullType;
use Chemisus\GraphQL\Repositories;
use Chemisus\GraphQL\Schema;
use React\Promise\FulfilledPromise;

class PersonSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $person = $schema->getType('Person');
        $person->addField(new Field($person, 'name', $schema->getType('String')));
        $person->addField(new Field($person, 'gender', $schema->getType('Gender')));
        $person->addField(new Field($person, 'father', new NonNullType($person)));
        $person->addField(new Field($person, 'mother', new NonNullType($person)));
        $person->addField(new Field($person, 'children', new ListType($person)));
        $person->addField(new Field($person, 'pets', new ListType($schema->getType('Animal'))));

        $person->field('father')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $fetched = Repositories::people()->fathersOf($node->parent()->items());

                foreach ($fetched as $person) {
                    $graph[$person->name] = $person;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) use (&$graph) {
                return $graph[$parent->father];
            }));

        $person->field('mother')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $fetched = Repositories::people()->mothersOf($node->parent()->items());

                foreach ($fetched as $person) {
                    $graph[$person->name] = $person;
                }

                return new FulfilledPromise($fetched);
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) use (&$graph) {
                return $graph[$parent->mother];
            }));

        $person->field('children')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $fetched = Repositories::people()->childrenOf($node->parent()->items());

                foreach ($fetched as $person) {
                    $graph[$person->name] = $person;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $person) use (&$graph) {
                return Repositories::people()->childrenOf([$person]);
            }));

        $person->field('pets')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $fetched = array_merge(...array_map(function ($person) {
                    return array_values(array_merge(
                        array_filter(Repositories::cats()->toArray(), function ($cat) use ($person) {
                            return $cat->owner === $person->name;
                        }),
                        array_filter(Repositories::dogs()->toArray(), function ($dog) use ($person) {
                            return $dog->owner === $person->name;
                        })
                    ));
                }, $node->parent()->items()));

                foreach ($fetched as $animal) {
                    $graph[$animal->name] = $animal;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) use (&$graph) {
                return array_values(array_filter($graph, function ($animal) use ($parent) {
                    return $animal->owner === $parent->name;
                }));
            }));
    }
}
