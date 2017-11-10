<?php

namespace Chemisus\GraphQL;

use Exception;
use PHPUnit\Framework\TestCase;
use React\Promise\FulfilledPromise;
use function React\Promise\reduce;

class SchemaTest extends TestCase
{
    public function documentProvider()
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
        return $queryBuilder->loadSource($gql)->buildDocument();
    }

    /**
     * @dataProvider documentProvider
     * @param string $schemaName
     * @param string $schemaSource
     * @param string $querySource
     * @param $result
     */
    public function testQuery(string $schemaName, string $schemaSource, string $querySource, $result)
    {
        echo PHP_EOL;

        $actual = $this->benchmark('total', function () use ($schemaName, $schemaSource, $querySource) {
            /**
             * @var DocumentBuilder $builder
             */
            $builder = $this->benchmark('init builder', function () {
                return new DocumentBuilder();
            });

            $executor = $this->benchmark('init executor', function () {
                return new DocumentExecutor();
            });

            $schemaWirer = $this->benchmark('init schema wirer', function () {
                return new IntrospectionDocumentWirer();
            });

            $testWirer = $this->benchmark('init test wirer', function () use ($schemaName) {
                switch ($schemaName) {
                    case 'basic':
                        return new BasicDocumentWirer();
                    case 'sw':
                        return new StarwarsDocumentWirer();
                }
            });

            $this->benchmark('load variables', function () use (&$builder) {
                $builder->loadVariables((object)[
                    'a' => 'A',
                    'b' => 1,
                    'c' => 0,
                    'd' => true,
                    'e' => false,
                    'f' => null,
                ]);
            });

            $this->benchmark('load query', function () use (&$builder, $querySource) {
                $builder->loadSource($querySource);
            });

            $this->benchmark('load schema', function () use (&$builder, $schemaSource) {
                $builder->loadSource($schemaSource);
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

            $this->benchmark('wire schema', function () use (&$schemaWirer, &$document) {
                $schemaWirer->wire($document);
            });

            $this->benchmark('wire starwars', function () use (&$schemaName, &$document, &$testWirer) {
                $testWirer->wire($document);
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
