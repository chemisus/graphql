<?php

namespace Chemisus\GraphQL;

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
        $document->resolver('Query', '__type', new CallbackResolver(function (Node $node) use ($document) {
            return $document->types[$node->getSelection()->getArguments()['name']];
        }));

        $document->coercer('__Schema', new CallbackCoercer(function (Node $node, Schema $value) {
            return (object)[
                'types' => [],
                'queryType' => $value->getQuery(),
                'mutationType' => $value->getMutation(),
                'subscriptionType' => null,
                'directives' => null,
            ];
        }));

        $document->coercer('__Type', new CallbackCoercer(function (Node $node, Type $value) {
            return (object)[
                'kind' => $value->getKind(),
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'fields' => $value->getFields(),
            ];
        }));

        $document->coercer('__Field', new CallbackCoercer(function (Node $node, Field $value) {
            return (object)[
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'type' => $value->getType(),
                'typeName' => $value->getTypeName(),
                'arguments' => $value->getArguments(),
                'directives' => $value->getDirectives(),
            ];
        }));

        if ($name === 'sw') {
            $graph = [];

            $document->fetcher('Query', 'allPeople', new CallbackFetcher(function (Node $node) use (&$graph) {
                $url = 'https://swapi.co/api/people/';
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
            }));

            $document->resolver('Query', 'allPeople', new CallbackResolver(function (Node $node) {
                return (object) [
                    'people' => $node->getItems(),
                ];
            }));

            $document->fetcher('Query', 'allPlanets', new CallbackFetcher(function (Node $node) use (&$graph) {
                $url = 'https://swapi.co/api/planets/';
                $dir = dirname(dirname(__DIR__)) . '/out/cache/';
                $key = base64_encode($url);
                $file = $dir . $key;

                if (file_exists($file)) {
                    return json_decode(file_get_contents($file))->results;
                }

                return Http::get('https://swapi.co/api/planets/')
                    ->then(function ($data) use ($dir, $file) {
                        if (!file_exists($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        file_put_contents($file, $data);
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
        $start = microtime(true);

        $builder = new DocumentBuilder();
        $executor = new DocumentExecutor();

        $instance = microtime(true);
        $builder->load($querySource);
        $loadQuery = microtime(true);
        $builder->load($schemaSource);
        $loadSchema = microtime(true);
        $document = $builder->build();
        $build = microtime(true);
        $this->wire($schemaName, $document);
        $wire = microtime(true);

        $actual = json_encode($executor->execute($document));
        $execute = microtime(true);

        $end = microtime(true);

//        printf("%2\$0.6f %1\$s\n", 'instance', $instance - $start);
//        printf("%2\$0.6f %1\$s\n", 'load query', $loadQuery - $instance);
//        printf("%2\$0.6f %1\$s\n", 'load schema', $loadSchema - $loadQuery);
//        printf("%2\$0.6f %1\$s\n", 'build', $build - $loadSchema);
//        printf("%2\$0.6f %1\$s\n", 'wire', $wire - $build);
//        printf("%2\$0.6f %1\$s\n", 'execute', $execute - $wire);
//        printf("%2\$0.6f %1\$s\n", 'total', $end - $start);

        $expect = $result;
        $this->assertEquals($expect, $actual);
    }
}
