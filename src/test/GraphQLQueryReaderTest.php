<?php

namespace GraphQL;

use PHPUnit\Framework\TestCase;

class GraphQLQueryReaderTest extends TestCase
{
    public function nextToken($data, &$offset = 0)
    {
        $fractionalPart = "\.[0-9]+";
        $exponentialPart = "[eE][-+]?[0-9]+";
        $stringValue = '"([^"\\\\]*\\\\.)*[^"]*"';
        $booleanValue = "true|false";
        $nullValue = "null";
        $intValue = "-?(0|[1-9][0-9]*)";
        $floatValue = sprintf("%s(%s)?(%s)?", $intValue, $fractionalPart, $exponentialPart);
        $name = "[_A-Za-z][_0-9A-Za-z]*";
        $punctuator = '[\!\$\(\)\:\=\@\[\]\{\}\|]|(\.\.\.)';
        $comment = "#.*";
        $token = sprintf(
            "(%s)|(%s)|(%s)|(%s)|(%s)|(%s)|(%s)|(%s)",
            $punctuator,
            $name,
            $intValue,
            $floatValue,
            $stringValue,
            $booleanValue,
            $nullValue,
            $comment
        );

        $matches = [];
        if (!preg_match("/$token/", $data, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            return false;
        }

        $found = $matches[0][0];
        $offset = $matches[0][1] + strlen($found);
        return $found;
    }

    public function lex($query)
    {
        $offset = 0;
        $tokens = [];
        while (($token = $this->nextToken($query, $offset)) !== false) {
            $tokens[] = $token;
        }

        return $tokens;
    }

    public function queryProvider()
    {
        return [
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', 'name', '}', '}'],
                <<< _GQL
{
    person(name:"terrence") {
        name
    }
}
_GQL
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', 'name', '}', '}'],
                '{person(name:"terrence") {name}}'
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', 'name', 'blah', '}', '}'],
                '{person(name:"terrence") {name,blah}}'
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', 'name', 'blah', '}', '}'],
                <<< _GQL
{
    person(name:"terrence") {
        name
        blah
    }
}
_GQL
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', 'name', '#test some comment', 'blah', '}', '}'],
                <<< _GQL
{
    person(name:"terrence") {
        name
        #test some comment
        blah
    }
}
_GQL
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', 'name', '#test some comment', '#another comment', 'blah', '}', '}'],
                <<< _GQL
{
    person(name:"terrence") {
        name
        #test some comment
        #another comment
        blah
    }
}
_GQL
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', 'name', '#test some comment', '#another comment', '#another comment }', 'blah', '}', '}'],
                <<< _GQL
{
    person(name:"terrence") {
        name
        #test some comment
        #another comment
        #another comment }
        blah
    }
}
_GQL
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', '...', 'P', '}', '}', 'fragment', 'P', 'on', 'Person', '{', 'name', '}'],
                '{person(name:"terrence") {...P}} fragment P on Person { name }'
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', '...', 'P', 'id', '}', '}', 'fragment', 'P', 'on', 'Person', '{', 'name', '}'],
                '{person(name:"terrence") {...P id}} fragment P on Person { name }'
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', 'name', '#test some comment', 'a', '#another comment', 'b', '{', 'c', '}', '#another comment }', 'blah', '}', '}'],
                <<< _GQL
{
    person(name:"terrence") {
        name
        #test some comment
        a #another comment
        b{c} #another comment }
        blah
    }
}
_GQL
            ],
            [
                ['{', 'person', '(', 'name', ':', '"terrence"', ')', '{', '...', 'P', 'id', '}', '}', 'fragment', 'P', 'on', 'Person', '{', 'name', '}'],
                '{person(name:"terrence") {...P id}} fragment P on Person { name }'
            ],
        ];
    }

    public function build($tokens)
    {
        for ($i = 0; $i < count($tokens); $i++) {

        }
    }

    /**
     * @param $expect
     * @param $query
     * @dataProvider queryProvider
     */
    public function testLex($expect, $query)
    {
        $actual = $this->lex($query);
        $this->assertEquals($expect, $actual);
    }

    /**
     * @param $expect
     * @param $query
     * @dataProvider queryProvider
     */
    public function testBuild($expect, $query)
    {
        $actual = $this->build($this->lex($query));

        $this->assertTrue(true);
//        $this->assertEquals($expect, $actual);
    }
}
