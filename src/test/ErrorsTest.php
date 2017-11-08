<?php

namespace Chemisus\GraphQL;

use Exception;
use PHPUnit\Framework\TestCase;

class ErrorsTest extends TestCase
{
    public function document($schema, $query, $variables = [])
    {
        $builder = new DocumentBuilder();
        $schemaWirer = new DocumentWirer();
        $builder->load($query);
        $builder->load($schema);

        $builder->loadVariables($variables);
        $builder->parse();
        $builder->buildSchema();
        $builder->buildOperations();
        $document = $builder->document();
        $schemaWirer->wire($document);
        return $document;
    }

    public function execute($schema, $query, $variables = [])
    {
        $document = $this->document($schema, $query, $variables);
        $executor = new DocumentExecutor();
        $executor->execute($document);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage variable NAME is undefined
     */
    public function testUndefinedVariable()
    {
        $schema = 'type Query {a(name:String):String}';
        $query = '{a(name:$NAME)}';
        $variables = [];

        $this->execute($schema, $query, $variables);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage variable NAME is undefined
     */
    public function testDocumentUndefinedVariable()
    {
        $schema = 'type Query {a(name:String):String}';
        $query = '{a(name:$NAME)}';
        $variables = [];

        $document = $this->document($schema, $query, $variables);
        $document->getVariable('NAME');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage variable a is already defined
     */
    public function testDocumentDefinedVariable()
    {
        $schema = 'type Query { __schema: __Schema,  __type: __Type } type A {a(name:String):String}';
        $query = '{a}';
        $variables = [];

        $document = $this->document($schema, $query, $variables);
        $document->setVariable('a', 'A');
        $document->setVariable('a', 'A');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage type USER is undefined
     */
    public function testUndefinedType()
    {
        $schema = 'type Query {a(name:String):USER}';
        $query = '{a(name:$a)}';
        $variables = [];

        $this->execute($schema, $query, $variables);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage type USER is undefined
     */
    public function testGetUndefinedType()
    {
        $schema = 'type Query { __schema: __Schema,  __type: __Type } type A {a(name:String):String}';
        $query = '{a}';
        $variables = [];

        $this->document($schema, $query, $variables)->getType('USER');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage type Query is already defined
     */
    public function testTypeDefined()
    {
        $schema = 'type Query {a(name:String):USER} type Query { a:String }';
        $query = '{a(name:$a)}';
        $variables = [];

        $this->execute($schema, $query, $variables);
    }

    /**
     * @expectedException Exception
     */
    public function testUndefinedFragment()
    {
        $schema = 'type Query {a(name:String):__Type}';
        $query = '{a { ... FRAGMENT } }';
        $variables = [];

        $this->execute($schema, $query, $variables);
    }
}
