<?php

namespace GraphQL;

use PHPUnit\Framework\TestCase;

class GraphQLTest extends TestCase
{
    public function testThis()
    {
        $people = [
            'terrence' => (object) [
                'name' => 'terrence',
                'mother' => 'gwen',
            ],
            'nick' => (object) [
                'name' => 'nick',
                'mother' => 'gwen',
                'father' => 'rob',
            ],
            'rob' => (object) [
                'name' => 'rob',
                'mother' => 'carol',
            ],
            'jessica' => (object) [
                'name' => 'jessica',
                'father' => 'mark',
                'mother' => 'sandra',
            ],
            'tom' => (object) [
                'name' => 'tom',
                'father' => 'carlton',
                'mother' => 'eileen',
            ],
            'gail' => (object) [
                'name' => 'gail',
                'father' => 'murial',
                'mother' => 'gilbert',
            ],
            'gwen' => (object) [
                'name' => 'gwen',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'courtney' => (object) [
                'name' => 'courtney',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'wade' => (object) [
                'name' => 'wade',
                'father' => 'tom',
                'mother' => 'gail',
            ],
        ];

        $graph = [];

        $schemaType = new ObjectType('Schema');
        $queryType = new ObjectType('Query');
        $stringType = new ScalarType('String');
        $personType = new ObjectType('Person');

        $schemaType->fields['query'] = new Field($schemaType, 'query', $queryType);

        $queryType->fields['greeting'] = new Field($queryType, 'greeting', $stringType);
        $queryType->fields['person'] = new Field($queryType, 'person', $personType);

        $personType->fields['name'] = new Field($personType, 'name', $stringType);
        $personType->fields['father'] = new Field($personType, 'father', $personType);
        $personType->fields['mother'] = new Field($personType, 'mother', $personType);
        $personType->fields['children'] = new Field($personType, 'children', new ListType($personType));

        $queryType->fields['greeting']->resolver = function (Node $node) {
            return sprintf("Hello, %s!\n", $node->arg('name', 'World'));
        };

        $schemaType->fields['query']->fetcher = function (Node $node) {
            return [true];
        };

        $schemaType->fields['query']->resolver = function (Node $node, $owner) {
            return $owner;
        };

        $queryType->fields['person']->fetcher = function (Node $node) use ($people) {
            return array_key_exists($node->arg('name'), $people) ? [$people[$node->arg('name')]] : null;
        };

        $queryType->fields['person']->resolver = function (Node $node) use ($people) {
            return $node->items()[0];
        };

        $personType->fields['children']->fetcher = function (Node $node) use ($people) {
            return array_filter(array_merge([], ...array_map(function ($person) use ($people) {
                return array_values(array_filter($people, function ($child) use ($person) {
                    return array_key_exists('father', $child) && $child->father === $person->name ||
                        array_key_exists('mother', $child) && $child->mother === $person->name;
                }));
            }, $node->parent()->items())));
        };

        $personType->fields['father']->fetcher = function (Node $node) use ($people) {
            return array_values(array_filter(array_map(function ($person) use ($people) {
                return array_key_exists($person->father, $people) ? $people[$person->father] : null;
            }, $node->parent()->items())));
        };

        $personType->fields['father']->resolver = function (Node $node, $value) use ($graph, $people) {
            return $people[$value];
        };

        $personType->fields['mother']->fetcher = function (Node $node) use ($people) {
            return array_values(array_filter(array_map(function ($person) use ($people) {
                return array_key_exists($person->mother, $people) ? $people[$person->mother] : null;
            }, $node->parent()->items())));
        };

        $personType->fields['mother']->resolver = function (Node $node, $value) use ($graph, $people) {
            return $people[$value];
        };

        $xml = <<< _XML
<query xmlns:gql="graphql">
    <greeting name="Terrence"/>
    <person gql:alias="gwen" name="gwen">
        <name/>
    </person>
    <person gql:alias="terrence" name="terrence">
        <name/>
        <mother gql:alias="mom">
            <name/>
            <mother gql:alias="mom">
                <name/>
                <mother gql:alias="mom">
                    <name/>
                    <mother gql:alias="mom">
                        <name/>
                    </mother>
                </mother>
            </mother>
        </mother>
    </person>
    <!--<person gql:alias="terrence" name="terrence">-->
        <!--<name/>-->
        <!--<father>-->
            <!--<name/>-->
        <!--</father>-->
        <!--<mother>-->
            <!--<name/>-->
            <!--<mother>-->
                <!--<name/>-->
            <!--</mother>-->
            <!--<children>-->
                <!--<name/>-->
                <!--<father>-->
                    <!--<name/>-->
                <!--</father>-->
            <!--</children>-->
        <!--</mother>-->
    <!--</person>-->
</query>
_XML;

        $queryBuilder = new XMLQueryReader();
        $query = $queryBuilder->read($xml);

        $root = new Node($schemaType->field($query->name()), $query);
        $nodes = [];
        $queue = [$root];

        while (count($queue)) {
            $node = array_shift($queue);
            array_push($nodes, $node);
            $children = $node->fetch();
            if (count($children)) {
                array_push($queue, ...$children);
            }
        }

        $value = $root->resolve((object) []);

        error_log(json_encode($value));

        $this->assertTrue(true);
    }
}
