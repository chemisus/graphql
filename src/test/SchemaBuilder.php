<?php

namespace GraphQL;

class SchemaBuilder
{
    /**
     * @var PeopleRepository
     */
    private $people;

    public function setupSchema(Schema $schema, &$graph, $people)
    {
        $schema->addField(new Field($schema, 'query', $schema->getType('Query')));

        $schema->field('query')->setFetcher(new CallbackFetcher(function (Node $node) {
            return [true];
        }));

        $schema->field('query')->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
            return $value;
        }));
    }

    public function setupQuery(Schema $schema, &$graph, $people)
    {
        $query = $schema->getType('Query');
        $query->addField(new Field($query, 'greeting', $schema->getType('String')));
        $query->addField(new Field($query, 'person', $schema->getType('Person')));
        $query->addField(new Field($query, 'people', new ListType($schema->getType('Person'))));

        $query->field('greeting')->setResolver(new CallbackResolver(function (Node $node) {
            return sprintf("Hello, %s!", $node->arg('name', 'World'));
        }));

        $query->field('person')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph, $people) {
                $name = $node->arg('name');
                $fetched = $this->people[$name];
                $graph[$name] = $fetched;
                return [$fetched];
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
                return $node->items()[0];
            }));

        $query->field('people')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph, $people) {
                $names = explode(',', $node->arg('names'));
                $fetched = array_map(function ($name) use (&$people) {
                    return array_key_exists($name, $people) ? $people[$name] : null;
                }, $names);

                foreach ($fetched as $item) {
                    $graph[$item->name] = $item;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
                return $node->items();
            }));
    }

    public function setupPerson(Schema $schema, &$graph, &$people)
    {
        $person = $schema->getType('Person');
        $person->addField(new Field($person, 'name', $schema->getType('String')));
        $person->addField(new Field($person, 'father', new NonNullType($person)));
        $person->addField(new Field($person, 'mother', new NonNullType($person)));
        $person->addField(new Field($person, 'children', new ListType($person)));

        $person->field('father')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph, $people) {
                $fetched = $this->people->fathersOf($node->parent()->items());

                foreach ($fetched as $person) {
                    $graph[$person->name] = $person;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) use (&$graph) {
                return $graph[$parent->father];
            }));

        $person->field('mother')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph, $people) {
                $fetched = $this->people->mothersOf($node->parent()->items());

                foreach ($fetched as $person) {
                    $graph[$person->name] = $person;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) use (&$graph) {
                return $graph[$parent->mother];
            }));

        $person->field('children')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph, $people) {
                $fetched = $this->people->childrenOf($node->parent()->items());

                foreach ($fetched as $person) {
                    $graph[$person->name] = $person;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $person) use (&$graph) {
                return $this->people->childrenOf([$person]);
            }));
    }
}