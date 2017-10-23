<?php

namespace GraphQL;

use GraphQL\Readers\GraphQLQueryReader;
use PHPUnit\Framework\TestCase;

class GraphQLQueryReaderTest extends TestCase
{
    public function test()
    {
        $gql = <<< _GQL
{
    person(name:"terrence") {
        name
    }
}
_GQL;


        $reader = new GraphQLQueryReader();
        $reader->read($gql);

        $this->assertTrue(true);
    }
}