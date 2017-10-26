<?php

namespace GraphQL;

class GraphQLQueryReader
{
    public function read($gql): Query
    {
        $n = strlen($gql);

        for ($i = 0; $i < $n; $i++) {
        }

        return new Query('query');
    }

    public function readQuery()
    {
    }
}
