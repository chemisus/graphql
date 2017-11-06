<?php

namespace Chemisus\GraphQL;

use PHPUnit\Framework\TestCase;
use function React\Promise\reduce;

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
                return $this->fetchURL('https://swapi.co/api/people/');
            }));

            $document->resolver('Query', 'allPeople', new CallbackResolver(function (Node $node) {
                return (object) [
                    'people' => $node->getItems(),
                ];
            }));

            $document->fetcher('Query', 'allPlanets', new CallbackFetcher(function (Node $node) use (&$graph) {
                return $this->fetchURL('https://swapi.co/api/planets/');
            }));

            $document->resolver('Query', 'allPlanets', new CallbackResolver(function (Node $node) {
                return (object) [
                    'planets' => $node->getItems(),
                ];
            }));

            $document->fetcher('Query', 'allItems', new CallbackFetcher(function (Node $node) use (&$graph) {
                echo "FETCHING\n";
                return reduce([
                    $this->fetchURL('https://swapi.co/api/people/'),
                    $this->fetchURL('https://swapi.co/api/planets/'),
                ], 'array_merge', []);
            }));

            $document->resolver('Query', 'allItems', new CallbackResolver(function (Node $node) {
                echo "RESOLVING\n";

                return $node->getItems();
            }));
        }
    }

    public function fetchURL(string $url)
    {
        $dir = dirname(dirname(__DIR__)) . '/out/cache/';
        $key = base64_encode($url);
        $file = $dir . $key;

        if (file_exists($file)) {
            return json_decode(file_get_contents($file))->results;
        }

        return Http::get($url)
            ->then(function ($data) use ($dir, $file) {
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($file, $data);
                return json_decode($data)->results;
            });
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
        echo PHP_EOL;

        $actual = $this->benchmark('total', function () use ($schemaName, $schemaSource, $querySource) {
            $builder = $this->benchmark('init builder', function () {
                return new DocumentBuilder();
            });

            $executor = $this->benchmark('init executor', function () {
                return new DocumentExecutor();
            });

            $wirer = $this->benchmark('init wirer', function () {
                return new DocumentWirer();
            });

            $this->benchmark('load query', function () use (&$builder, $querySource) {
                $builder->load($querySource);
            });

            $this->benchmark('load schema', function () use (&$builder, $schemaSource) {
                $builder->load($schemaSource);
            });

            $this->benchmark('parse', function () use (&$builder) {
                $builder->parse();
            });

            $this->benchmark('build schema', function () use (&$builder) {
                $builder->buildSchema();

            });

            $this->benchmark('build operations', function () use (&$builder) {
                $builder->buildOperations();
            });

            $document = $this->benchmark('document', function () use (&$builder) {
                return $builder->document();
            });

            $this->benchmark('wire introspection', function () use (&$wirer, &$document) {
                $wirer->wire($document);
            });

            $this->benchmark('wire schema', function () use (&$schemaName, &$document) {
                $this->wire($schemaName, $document);
            });

            return $this->benchmark('execute', function () use (&$executor, &$document) {
                return json_encode($executor->execute($document));
            });
        });

        $expect = $result;
        $this->assertEquals($expect, $actual);
    }

    public function benchmark($label, callable $callback)
    {
        $start = microtime(true);
        $result = call_user_func($callback);
        $end = microtime(true);
        printf("%2\$10.6f %1\$s\n", $label, $end - $start);
        return $result;
    }
}
