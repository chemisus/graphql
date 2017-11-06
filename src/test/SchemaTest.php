<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Schema;
use GraphQL\Language\Parser;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    public function gqlProvider()
    {
        $schemaFiles = glob(dirname(dirname(__DIR__)) . '/resources/test/schema/*.gql');

        return array_merge([], ...array_map(function ($schemaFile) {
            $schemaSource = file_get_contents($schemaFile);
            $schema = $this->make($schemaSource);
            $schemaName = str_replace('.gql', '', basename($schemaFile));

            $queryFiles = glob(dirname(dirname(__DIR__)) . '/resources/test/schema/' . $schemaName . '/*.gql');

            return array_merge([], ...array_map(function ($queryFile) use ($schemaName, $schemaFile, $schemaSource, $schema) {
                $querySource = file_get_contents($queryFile);
                $queryName = str_replace('.gql', '', basename($queryFile));

                $resultFile = str_replace('.gql', '.json', $queryFile);
                $result = json_encode(json_decode(file_get_contents($resultFile)));

                return [
                    sprintf("%s::%s", $schemaName, $queryName) => [
                        $schemaSource,
                        $querySource,
                        $result,
                    ]
                ];
            }, $queryFiles));
        }, $schemaFiles));
    }

    public function make($gql)
    {
        $queryBuilder = new DocumentBuilder();
        return $queryBuilder->load($gql)->build();
    }

    public function wire($name, Document $document)
    {
        if ($name === 'sw') {
            $graph = [];

            $document->fetcher('Query', 'allPeople', new CallbackFetcher(function (Node $node) use (&$graph) {
                    return Http::get('https://swapi.co/api/people/')
                        ->then(function ($data) {
                            return json_decode($data)->results;
                        });
                }));

            $document->resolver('Query', 'allPeople', new CallbackResolver(function (Node $node) {
                    return (object) [
                        'people' => $node->items(),
                    ];
                }));

            $document->fetcher('Query', 'allPeople', new CallbackFetcher(function (Node $node) use (&$graph) {
                    return Http::get('https://swapi.co/api/planets/')
                        ->then(function ($data) {
                            return json_decode($data)->results;
                        });
                }));

            $document->resolver('Query', 'allPeople', new CallbackResolver(function (Node $node) {
                    return (object) [
                        'planets' => $node->items(),
                    ];
                }));
        }
    }

    /**
     * @dataProvider gqlProvider
     * @param string $schema
     * @param string $query
     * @param mixed $result
     */
    public function testQuery($schema, $query, $result)
    {
        $builder = new DocumentBuilder();
        $wirer = new DocumentWirer();
        $executor = new DocumentExecutor();
        $builder->load($query);
        $builder->load($schema);
        $document = $builder->build();
        $wirer->wire($document);

        $expect = $result;
        $actual = json_encode($executor->execute($document));

        $this->assertEquals($expect, $actual);
    }
}
