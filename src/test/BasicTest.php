<?php

namespace GraphQL;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var PeopleRepository
     */
    private $people;

    /**
     * @var Repository
     */
    private $cats;

    /**
     * @var Repository
     */
    private $dogs;

    public function setupSchema(Schema $schema, &$graph)
    {
        $schema->addField(new Field($schema, 'query', $schema->getType('Query')));

        $schema->field('query')->setFetcher(new CallbackFetcher(function (Node $node) {
            return [true];
        }));

        $schema->field('query')->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
            return $value;
        }));
    }

    public function setupQuery(Schema $schema, &$graph)
    {
        $query = $schema->getType('Query');
        $query->addField(new Field($query, 'greeting', $schema->getType('String')));
        $query->addField(new Field($query, 'person', $schema->getType('Person')));
        $query->addField(new Field($query, 'people', new ListType($schema->getType('Person'))));
        $query->addField(new Field($query, 'animals', new ListType($schema->getType('Animal'))));

        $query->field('greeting')->setResolver(new CallbackResolver(function (Node $node) {
            return sprintf("Hello, %s!", $node->arg('name', 'World'));
        }));

        $query->field('person')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $name = $node->arg('name');
                $fetched = $this->people[$name];
                $graph[$name] = $fetched;
                return [$fetched];
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
                return $node->items()[0];
            }));

        $query->field('people')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                $names = explode(',', $node->arg('names'));
                $fetched = $this->people->gets(...$names);

                foreach ($fetched as $item) {
                    $graph[$item->name] = $item;
                }

                return $fetched;
            }))
            ->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
                return $node->items();
            }));
    }

    public function setupPerson(Schema $schema, &$graph)
    {
        $person = $schema->getType('Person');
        $person->addField(new Field($person, 'name', $schema->getType('String')));
        $person->addField(new Field($person, 'father', new NonNullType($person)));
        $person->addField(new Field($person, 'mother', new NonNullType($person)));
        $person->addField(new Field($person, 'children', new ListType($person)));

        $person->field('father')
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
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
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
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
            ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
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

    public function setupAnimal(Schema $schema, &$graph)
    {
        $animal = $schema->getType('Animal');
        $animal->addField(new Field($animal, 'name', $schema->getType('String')));

        $animal->typer = new CallbackTyper(function (Node $node, $value) {
            return $node->schema()->getType($value->type);
        });
    }

    public function setupDog(Schema $schema, &$graph)
    {
        $dog = $schema->getType('Dog');
        $dog->addField(new Field($dog, 'name', $schema->getType('String')));
        $dog->addField(new Field($dog, 'guard', $schema->getType('Boolean')));
    }

    public function setupCat(Schema $schema, &$graph)
    {
        $cat = $schema->getType('Cat');
        $cat->addField(new Field($cat, 'name', $schema->getType('String')));
        $cat->addField(new Field($cat, 'lives', $schema->getType('Integer')));
    }

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->people = PeopleRepository::Sample();

        $graph = [];

        $schema = new Schema('Schema');

        $schema->putType(new ScalarType('String'));
        $schema->putType(new ScalarType('Integer'));
        $schema->putType(new ScalarType('Boolean'));
        $schema->putType(new ObjectType('Query'));
        $schema->putType(new ObjectType('Person'));
        $schema->putType(new InterfaceType('Animal'));
        $schema->putType(new ObjectType('Dog'));
        $schema->putType(new ObjectType('Cat'));

        $this->setupSchema($schema, $graph);
        $this->setupQuery($schema, $graph);
        $this->setupPerson($schema, $graph);
        $this->setupAnimal($schema, $graph);
        $this->setupDog($schema, $graph);
        $this->setupCat($schema, $graph);

        $this->schema = $schema;
    }

    public function queryXML(string $xml)
    {
        $queryBuilder = new XMLQueryReader();
        $query = $queryBuilder->read($xml);
        $executor = new BFSExecutor();
        return $executor->execute($this->schema, $query);
    }

    public function caseProvider()
    {
        $xmls = glob(dirname(dirname(__DIR__)) . '/resources/test/*.xml');
        return array_merge([], ...array_map(function ($xml) {
            return [
                basename($xml) => [
                    file_get_contents($xml),
                    json_decode(file_get_contents(str_replace('.xml', '.json', $xml))),
                ]
            ];
        }, $xmls));
    }

    /**
     * @dataProvider caseProvider
     */
    public function testQuery($xml, $json)
    {
        $actual = $this->queryXML($xml);
        $expect = $json;

        $this->assertEquals(json_encode($expect), json_encode($actual));
    }
}
