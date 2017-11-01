<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\Parser;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    public function gqlProvider()
    {
        $schemaFiles = glob(dirname(dirname(__DIR__)) . '/resources/test/schema/*.gql');

        return array_merge([], ...array_map(function ($schemaFile) {
            $schemaSource = file_get_contents($schemaFile);
            $schema = $this->makeSchema($schemaSource);
            $schemaName = str_replace('.gql', '', basename($schemaFile));
            $this->wireSchema($schemaName, $schema);

            $queryFiles = glob(dirname(dirname(__DIR__)) . '/resources/test/schema/' . $schemaName . '/*.gql');

            return array_merge([], ...array_map(function ($queryFile) use ($schemaName, $schemaFile, $schemaSource, $schema) {
                $querySource = file_get_contents($queryFile);
                $query = $this->makeQuery($querySource, $schema);
                $queryName = str_replace('.gql', '', basename($queryFile));

                $resultFile = str_replace('.gql', '.json', $queryFile);
                $result = json_encode(json_decode(file_get_contents($resultFile)));

                return [
                    sprintf("%s::%s", $schemaName, $queryName) => [
                        $schemaSource,
                        $schema,
                        $querySource,
                        $query,
                        $result,
                    ]
                ];
            }, $queryFiles));
        }, $schemaFiles));
    }

    public function makeSchema($gql)
    {
        $queryBuilder = new GraphQLSchemaBuilder();
        return $queryBuilder->buildSchema(json_decode(json_encode(Parser::parse($gql)->toArray(true))));
    }

    public function wireSchema($name, Schema $schema)
    {
        if ($name === 'sw') {
            $graph = [];

            $schema->queryType()
                ->field('allPeople')
                ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                    return Http::get('https://swapi.co/api/people/')
                        ->then(function ($data) {
                            return json_decode($data)->results;
                        });
                }))
                ->setResolver(new CallbackResolver(function (Node $node) {
                    return (object) [
                        'people' => $node->items(),
                    ];
                }));

            $schema->queryType()
                ->field('allPlanets')
                ->setFetcher(new CallbackFetcher(function (Node $node) use (&$graph) {
                    return Http::get('https://swapi.co/api/planets/')
                        ->then(function ($data) {
                            return json_decode($data)->results;
                        });
                }))
                ->setResolver(new CallbackResolver(function (Node $node) {
                    return (object) [
                        'planets' => $node->items(),
                    ];
                }));
        }
    }

    public function makeQuery($gql, $schema)
    {
        $queryBuilder = new GQLQueryReader();
        return $queryBuilder->read($schema, $gql);
    }

    /**
     * @dataProvider gqlProvider
     * @param string $schemaSource
     * @param Schema $schema
     */
    public function testSchema(string $schemaSource, Schema $schema, string $querySource, Query $query, $result)
    {
        $expect = implode(PHP_EOL, array_filter(explode(PHP_EOL, trim($schemaSource))));
        $actual = (string) $schema;

        $this->assertEquals($expect, $actual);
    }

    public function queryReact(Schema $schema, Query $query)
    {
        $executor = new ReactExecutor();
        return $executor->execute($schema, $query);
    }

    /**
     * @dataProvider gqlProvider
     * @param string $schemaSource
     * @param Schema $schema
     */
    public function testQuery(string $schemaSource, Schema $schema, string $querySource, Query $query, string $result)
    {
        $expect = $result;
        $actual = json_encode($this->queryReact($schema, $query));

        $this->assertEquals($expect, $actual);
    }
}
