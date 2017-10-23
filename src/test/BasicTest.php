<?php

namespace GraphQL;

use GraphQL\Setup\AnimalSetup;
use GraphQL\Setup\CatSetup;
use GraphQL\Setup\DogSetup;
use GraphQL\Setup\PersonSetup;
use GraphQL\Setup\PetSetup;
use GraphQL\Setup\QuerySetup;
use GraphQL\Setup\SchemaSetup;
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    /**
     * @var Schema
     */
    private $schema;

    public function setUp()
    {
        parent::setUp();

        $graph = [];

        $this->schema = new Schema('Schema');

        $this->schema->putType(new ScalarType('String'));
        $this->schema->putType(new ScalarType('Integer'));
        $this->schema->putType(new ScalarType('Boolean'));
        $this->schema->putType(new ObjectType('Query'));
        $this->schema->putType(new ObjectType('Person'));
        $this->schema->putType(new UnionType('Pet'));
        $this->schema->putType(new InterfaceType('Animal'));
        $this->schema->putType(new ObjectType('Dog'));
        $this->schema->putType(new ObjectType('Cat'));

        $setups = [
            new SchemaSetup(),
            new QuerySetup(),
            new PersonSetup(),
            new AnimalSetup(),
            new PetSetup(),
            new DogSetup(),
            new CatSetup(),
        ];

        foreach ($setups as $setup) {
            $setup->setup($this->schema, $graph);
        }
    }

    public function caseProvider()
    {
        $files = glob(dirname(dirname(__DIR__)) . '/resources/test/*.xml');
        return array_merge([], ...array_map(function ($xml) {
            return [
                basename($xml) => [
                    file_get_contents($xml),
                    json_decode(file_get_contents(str_replace('.xml', '.json', $xml))),
                ]
            ];
        }, $files));
    }

    public function queryXML(string $xml)
    {
        $queryBuilder = new XMLQueryReader();
        $query = $queryBuilder->read($xml);
        $executor = new BFSExecutor();
        return $executor->execute($this->schema, $query);
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
