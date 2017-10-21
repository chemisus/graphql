<?php

namespace GraphQL;

use PHPUnit\Framework\TestCase;

class GraphQLTest extends TestCase
{
    public function testThis()
    {
        $people = [
            'terrence' => [
                'name' => 'terrence',
                'mother' => 'gwen',
            ],
            'nick' => [
                'name' => 'nick',
                'mother' => 'gwen',
                'father' => 'rob',
            ],
            'rob' => [
                'name' => 'rob',
                'mother' => 'carol',
            ],
            'jessica' => [
                'name' => 'jessica',
                'father' => 'mark',
                'mother' => 'sandra',
            ],
            'tom' => [
                'name' => 'tom',
                'father' => 'opa',
                'mother' => 'oma',
            ],
            'gail' => [
                'name' => 'gail',
                'father' => 'murial',
                'mother' => 'gilbert',
            ],
            'gwen' => [
                'name' => 'gwen',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'courtney' => [
                'name' => 'courtney',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'wade' => [
                'name' => 'wade',
                'father' => 'tom',
                'mother' => 'gail',
            ],
        ];

        $schemaType = new ObjectType('Schema');
        $queryType = new ObjectType('Query');
        $stringType = new ObjectType('String');
        $personType = new ObjectType('Person');

        $schemaType->fields['query'] = new Field($schemaType, 'query', $queryType);

        $queryType->fields['person'] = new Field($queryType, 'person', $personType);

        $personType->fields['name'] = new Field($personType, 'name', $stringType);
        $personType->fields['father'] = new Field($personType, 'father', $personType);
        $personType->fields['mother'] = new Field($personType, 'mother', $personType);
        $personType->fields['children'] = new Field($personType, 'children', new ListType($personType));

        $schemaType->fields['query']->fetcher = function (Node $node) {
            echo $node->path() . PHP_EOL;
            return [true];
        };

        $queryType->fields['person']->fetcher = function (Node $node) use ($people) {
            echo $node->path() . PHP_EOL;
            return array_key_exists($node->arg('name'), $people) ? [$people[$node->arg('name')]] : null;
        };

        $personType->fields['name']->fetcher = function (Node $node) {
            echo $node->path() . ': ' . json_encode(array_column($node->parent()->items(), 'name')) . PHP_EOL;
        };

        $personType->fields['children']->fetcher = function (Node $node) use ($people) {
            echo $node->path() . ': ' . json_encode(array_column($node->parent()->items(), 'name')) . PHP_EOL;
            return array_filter(array_merge([], ...array_map(function ($person) use ($people) {
                return array_values(array_filter($people, function ($child) use ($person) {
                    return array_key_exists('father', $child) && $child['father'] === $person['name'] ||
                        array_key_exists('mother', $child) && $child['mother'] === $person['name'];
                }));
            }, $node->parent()->items())));
        };

        $personType->fields['father']->fetcher = function (Node $node) use ($people) {
            echo $node->path() . ': ' . json_encode(array_column($node->parent()->items(), 'name')) . PHP_EOL;
            return array_values(array_filter(array_map(function ($person) use ($people) {
                return array_key_exists($person['father'], $people) ? $people[$person['father']] : null;
            }, $node->parent()->items())));
        };

        $personType->fields['mother']->fetcher = function (Node $node) use ($people) {
            echo $node->path() . ': ' . json_encode(array_column($node->parent()->items(), 'name')) . PHP_EOL;
            return array_values(array_filter(array_map(function ($person) use ($people) {
                return array_key_exists($person['mother'], $people) ? $people[$person['mother']] : null;
            }, $node->parent()->items())));
        };

        $xml = <<< _XML
<query>
    <person name="terrence">
        <name/>
        <father>
            <name/>
        </father>
        <mother>
            <name/>
            <mother>
                <name/>
            </mother>
            <children>
                <name/>
                <father>
                    <name/>
                </father>
            </children>
        </mother>
    </person>
</query>
_XML;

        $queryBuilder = new XMLQueryReader();
        $query = $queryBuilder->read($xml);

        $nodes = [];
        $queue = [new Node($schemaType->field($query->name()), $query)];

        while (count($queue)) {
            $node = array_shift($queue);
            $children = $node->fetch();
            if (count($children)) {
                array_push($queue, ...$children);
                array_push($nodes, ...$children);
            }
        }

        echo json_encode(array_map(function (Node $node) {
            return $node->items() === null ? null : array_map(function ($person) {
                return $person['name'];
            }, $node->items());
        }, $nodes));

        $this->assertTrue(true);
    }
}
