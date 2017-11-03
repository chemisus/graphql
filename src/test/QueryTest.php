<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Language\Parser;
use Chemisus\GraphQL\Setup\AnimalSetup;
use Chemisus\GraphQL\Setup\CatSetup;
use Chemisus\GraphQL\Setup\DogSetup;
use Chemisus\GraphQL\Setup\GenderSetup;
use Chemisus\GraphQL\Setup\PersonSetup;
use Chemisus\GraphQL\Setup\PetSetup;
use Chemisus\GraphQL\Setup\QuerySetup;
use Chemisus\GraphQL\Setup\ReactSetup;
use Chemisus\GraphQL\Setup\SchemaSetup;
use Chemisus\GraphQL\Types\EnumType;
use Chemisus\GraphQL\Types\InterfaceType;
use Chemisus\GraphQL\Types\ObjectType;
use Chemisus\GraphQL\Types\Schema;
use Chemisus\GraphQL\Types\UnionType;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
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
        $queryBuilder = new GQLQueryReader($this->schema);
        return $queryBuilder->read($xml);
    }

    public function queryBFS(Selection $query)
    {
        $executor = new BFSExecutor();
        return $executor->execute($this->schema, $query);
    }

    public function queryReact(Selection $query)
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
