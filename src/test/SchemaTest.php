<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Schema;
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
                        $schemaName,
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
                        'people' => $node->getItems(),
                    ];
                }));

            $document->fetcher('Query', 'allPlanets', new CallbackFetcher(function (Node $node) use (&$graph) {
                    return Http::get('https://swapi.co/api/planets/')
                        ->then(function ($data) {
                            return json_decode($data)->results;
                        });
                }));

            $document->resolver('Query', 'allPlanets', new CallbackResolver(function (Node $node) {
                    return (object) [
                        'planets' => $node->getItems(),
                    ];
                }));
        }
    }

    /**
     * @dataProvider gqlProvider
     * @param string $schemaName
     * @param string $schemaSource
     * @param string $querySource
     * @param $result
     */
    public function testQuery(string $schemaName, string $schemaSource, string $querySource, $result)
    {
        error_reporting(E_ALL);

        $builder = new DocumentBuilder();
        $executor = new DocumentExecutor();
        $builder->load($querySource);
        $builder->load($schemaSource);
        $document = $builder->build();
        $this->wire($schemaName, $document);

        $expect = $result;
        $actual = json_encode($executor->execute($document));

        $this->assertEquals($expect, $actual);
    }
}
