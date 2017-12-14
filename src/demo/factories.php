<?php

namespace Chemisus\GraphQL;

interface DirectiveFactory
{
    public function makeDirective(string $name, $args);
}

interface DirectiveDefinitionFactory
{
    public function makeDirectiveDefinition(string $name, $args, $locations);
}

interface ArgumentDefinitionFactory
{
    public function makeArgumentDefinition(string $name, $type, $defaultValue);
}

interface FieldDefinitionFactory
{
    public function makeFieldDefinition(string $name, $type, $args, $directives);
}

interface FieldFactory
{
    public function makeField(string $name, $alias, $args, $fields, $directives);
}

interface ScalarDefinitionFactory
{
    public function makeScalarDefinition(string $name, $directives = []);
}

interface UnionDefinitionFactory
{
    public function makeUnionDefinition(string $name, $types, $directives = []);
}

interface TypeFactory
{
    public function makeType(string $name);
}

interface TypeDefinitionFactory
{
    public function makeTypeDefinition(string $name, $fields, $directives = []);
}

interface SchemaDefinitionFactory
{
    public function makeSchemaDefinition(string $name, $fields, $directives = []);
}

interface InterfaceDefinitionFactory
{
    public function makeInterfaceDefinition(string $name, $fields, $directives = []);
}


interface AbstractFactory extends
    DirectiveFactory,
    DirectiveDefinitionFactory,
    ArgumentDefinitionFactory,
    FieldDefinitionFactory,
    FieldFactory,
    ScalarDefinitionFactory,
    UnionDefinitionFactory,
    TypeFactory,
    TypeDefinitionFactory,
    SchemaDefinitionFactory,
    InterfaceDefinitionFactory
{
}

class ChemisusAbstractFactory implements AbstractFactory
{
    public function make($function, $arguments)
    {
        $name = __NAMESPACE__ . '\\' . substr($function, 4);

        return new $name(...$arguments);
    }

    public function makeDirective(string $name, $args)
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeDirectiveDefinition(string $name, $args, $locations)
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeArgumentDefinition(string $name, $type, $defaultValue)
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeFieldDefinition(string $name, $type, $args, $directives)
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeField(string $name, $alias, $args, $fields, $directives)
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeScalarDefinition(string $name, $directives = [])
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeUnionDefinition(string $name, $types, $directives = [])
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeType(string $name)
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeTypeDefinition(string $name, $fields, $directives = [])
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeSchemaDefinition(string $name, $fields, $directives = [])
    {
        return $this->make(__FUNCTION__, func_get_args());
    }

    public function makeInterfaceDefinition(string $name, $fields, $directives = [])
    {
        return $this->make(__FUNCTION__, func_get_args());
    }
}

class Directive
{
    private $name;
    private $args;

    /**
     * Directive constructor.
     * @param $name
     * @param $args
     */
    public function __construct(string $name, $args)
    {
        $this->name = $name;
        $this->args = $args;
    }
}

class DirectiveDefinition
{
    private $name;
    private $args;
    private $locations;

    /**
     * Directive constructor.
     * @param $name
     * @param $args
     * @param $locations
     */
    public function __construct(string $name, $args, $locations)
    {
        $this->name = $name;
        $this->args = $args;
        $this->locations = $locations;
    }
}

class ArgumentDefinition
{
    private $name;
    private $type;
    private $defaultValue;

    /**
     * Directive constructor.
     * @param $name
     * @param $type
     * @param $defaultValue
     */
    public function __construct(string $name, $type, $defaultValue)
    {
        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
    }
}

class FieldDefinition
{
    private $name;
    private $type;
    private $args;
    private $directives;

    public function __construct(string $name, $type, $args, $directives)
    {
        $this->name = $name;
        $this->type = $type;
        $this->args = $args;
        $this->directives = $directives;
    }
}

class Field
{
    private $name;
    private $alias;
    private $args;
    private $directives;
    private $fields;

    public function __construct(string $name, $alias, $args, $fields, $directives)
    {
        $this->name = $name;
        $this->alias = $alias;
        $this->args = $args;
        $this->directives = $directives;
        $this->fields = $fields;
    }
}

class ScalarDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $directives;

    public function __construct(string $name, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
    }
}

class UnionDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $directives;
    private $types;

    public function __construct(string $name, $types, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
        $this->types = $types;
    }
}

class Type
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

class TypeDefinition
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $directives;

    private $fields;

    public function __construct(string $name, $fields, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
        $this->fields = $fields;
    }
}

class SchemaDefinition
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $directives;

    private $fields;

    public function __construct(string $name, $fields, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
        $this->fields = $fields;
    }
}

class InterfaceDefinition
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $directives;

    private $fields;

    public function __construct(string $name, $fields, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
        $this->fields = $fields;
    }
}
