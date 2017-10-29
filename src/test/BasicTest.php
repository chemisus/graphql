<?php

namespace GraphQL;

use GraphQL\Language\Parser;
use GraphQL\Setup\AnimalSetup;
use GraphQL\Setup\CatSetup;
use GraphQL\Setup\DogSetup;
use GraphQL\Setup\GenderSetup;
use GraphQL\Setup\PersonSetup;
use GraphQL\Setup\PetSetup;
use GraphQL\Setup\QuerySetup;
use GraphQL\Setup\ReactSetup;
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

        $this->schema = new Schema();

        $this->schema->putType(new ObjectType('Person'));
        $this->schema->putType(new EnumType('Gender'));
        $this->schema->putType(new UnionType('Pet'));
        $this->schema->putType(new InterfaceType('Animal'));
        $this->schema->putType(new ObjectType('Dog'));
        $this->schema->putType(new ObjectType('Cat'));
        $this->schema->putType(new ObjectType('React'));

        $setups = [
            new SchemaSetup(),
            new QuerySetup(),
            new PersonSetup(),
            new GenderSetup(),
            new AnimalSetup(),
            new PetSetup(),
            new DogSetup(),
            new CatSetup(),
            new ReactSetup(),
        ];

        foreach ($setups as $setup) {
            $setup->setup($this->schema, $graph);
        }
    }

    public function xmlProvider()
    {
        $files = glob(dirname(dirname(__DIR__)) . '/resources/test/query/*.xml');
        return array_merge([], ...array_map(function ($xml) {
            return [
                basename($xml) => [
                    file_get_contents($xml),
                    json_decode(file_get_contents(str_replace('.xml', '.json', $xml))),
                ]
            ];
        }, $files));
    }

    public function gqlProvider()
    {
        $files = glob(dirname(dirname(__DIR__)) . '/resources/test/query/*.gql');
        return array_merge([], ...array_map(function ($xml) {
            return [
                basename($xml) => [
                    file_get_contents($xml),
                    json_decode(file_get_contents(str_replace('.gql', '.json', $xml))),
                ]
            ];
        }, $files));
    }

    public function readXML($xml)
    {
        $queryBuilder = new XMLQueryReader();
        return $queryBuilder->read($this->schema, $xml);
    }

    public function readGQL($xml)
    {
        $queryBuilder = new GQLQueryReader();
        return $queryBuilder->read($this->schema, $xml);
    }

    public function queryBFS(Query $query)
    {
        $executor = new BFSExecutor();
        return $executor->execute($this->schema, $query);
    }

    public function queryReact(Query $query)
    {
        $executor = new ReactExecutor();
        return $executor->execute($this->schema, $query);
    }

    /**
     * @dataProvider xmlProvider
     * @param $xml
     * @param $expect
     */
    public function testXMLwithBFS($xml, $expect)
    {
        $actual = $this->queryBFS($this->readXML($xml));

        $this->assertEquals(json_encode($expect), json_encode($actual));
    }

    /**
     * @dataProvider xmlProvider
     * @param $xml
     * @param $expect
     */
    public function testGQLFromXMLwithBFS($xml, $expect)
    {
        $gql = $this->readXML($xml)->toString();
        $actual = $this->queryBFS($this->readGQL($gql));

        $this->assertEquals(json_encode($expect), json_encode($actual));
    }

    /**
     * @dataProvider gqlProvider
     * @param $gql
     * @param $expect
     */
    public function testGQLwithBFS($gql, $expect)
    {
        $actual = $this->queryBFS($this->readGQL($gql));

        $this->assertEquals(json_encode($expect), json_encode($actual));
    }

    /**
     * @dataProvider xmlProvider
     * @param $xml
     * @param $expect
     */
    public function testXMLwithReact($xml, $expect)
    {
        $actual = $this->queryReact($this->readXML($xml));

        $this->assertEquals(json_encode($expect), json_encode($actual));
    }

    /**
     * @dataProvider xmlProvider
     * @param $xml
     * @param $expect
     */
    public function testGQLFromXMLwithReact($xml, $expect)
    {
        $gql = $this->readXML($xml)->toString();
        $actual = $this->queryReact($this->readGQL($gql));

        $this->assertEquals(json_encode($expect), json_encode($actual));
    }

    /**
     * @dataProvider gqlProvider
     * @param $gql
     * @param $expect
     */
    public function testGQLwithReact($gql, $expect)
    {
        $actual = $this->queryReact($this->readGQL($gql));

        $this->assertEquals(json_encode($expect), json_encode($actual));
    }
}
