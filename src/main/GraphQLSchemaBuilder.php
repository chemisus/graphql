<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Builders\FieldBuilder;
use Chemisus\GraphQL\Builders\ListTypeBuilder;
use Chemisus\GraphQL\Builders\NamedTypeBuilder;
use Chemisus\GraphQL\Builders\NonNullTypeBuilder;
use Chemisus\GraphQL\Builders\ObjectTypeBuilder;
use Chemisus\GraphQL\Builders\ScalarTypeBuilder;

class GraphQLSchemaBuilder
{
    private $builders;

    public function __construct()
    {
        $this->readers = $readers = [
            'operationTypes' => function ($definition) {
                return $this->builds($definition->operationTypes, $definition);
            },
            'name' => function ($definition) {
                return $this->build($definition->name, $definition);
            },
            'description' => function ($definition) {
                return $this->description($definition);
            },
            'directives' => function ($definition) {
                return $this->builds($definition->directives, $definition);
            },
            'arguments' => function ($definition) {
                return $this->builds($definition->arguments, $definition);
            },
            'fields' => function ($definition) {
                return $this->builds($definition->fields, $definition);
            },
            'interfaces' => function ($definition) {
                return $this->builds($definition->interfaces, $definition);
            },
            'type' => function ($definition) {
                return $this->build($definition->type, $definition);
            },
            'values' => function ($definition) {
                return $this->builds($definition->values, $definition);
            },
            'types' => function ($definition) {
                return $this->builds($definition->types, $definition);
            },
            'defaultValue' => function ($definition) {
                return $definition->defaultValue;
            },
            'operation' => function ($definition) {
                return $definition->operation;
            },
        ];

        $this->builders = $builders = [
            'Document' => function ($definition, $parent) {
                return $this->builds($definition->definitions, $definition);
            },

            'StringValue' => function ($definition, $parent) {
                return $definition->value;
            },
            'BooleanValue' => function ($definition, $parent) {
                return $definition->value;
            },
            'IntValue' => function ($definition, $parent) {
                return $definition->value;
            },
            'FloatValue' => function ($definition, $parent) {
                return $definition->value;
            },
            'NullValue' => function ($definition, $parent) {
                return null;
            },

            'SchemaDefinition' => function ($definition, $parent) {
                $directives = $this->builds($definition->directives, $definition);
                $operationTypes = $this->builds($definition->operationTypes, $definition);
            },
            'NamedType' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);

                $builder = new NamedTypeBuilder();
                $builder->setName($name);
                return $builder;
            },
            'FieldDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $arguments = $this->builds($definition->arguments, $definition);
                $directives = $this->builds($definition->directives, $definition);
                $type = $this->build($definition->type, $definition);

                $builder = new FieldBuilder();
                $builder->setName($name);
                $builder->setType($type);
                return $builder;
            },
            'ObjectTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $fields = $this->builds($definition->fields, $definition);
                $interfaces = $this->builds($definition->interfaces, $definition);

                $builder = new ObjectTypeBuilder();
                $builder->setName($name);
                $builder->setFields($fields);
                return $builder;
            },
            'ScalarTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);

                $builder = new ScalarTypeBuilder();
                $builder->setName($name);
                $builder->setDescription($description);
                return $builder;
            },
            'NonNullType' => function ($definition, $parent) {
                $type = $this->build($definition->type, $definition);

                $builder = new NonNullTypeBuilder();
                $builder->setType($type);
                return $builder;
            },
            'ListType' => function ($definition, $parent) {
                $type = $this->build($definition->type, $definition);

                $builder = new ListTypeBuilder();
                $builder->setType($type);
                return $builder;
            },
            'EnumTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $values = $this->builds($definition->values, $definition);
            },
            'InterfaceTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $fields = $this->builds($definition->fields, $definition);
            },
            'InputObjectTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $fields = $this->builds($definition->fields, $definition);
            },
            'UnionTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $types = $this->builds($definition->types, $definition);
            },
            'EnumValueDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'InputValueDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $type = $this->build($definition->type, $definition);
                $defaultValue = $definition->defaultValue;
            },
            'OperationTypeDefinition' => function ($definition, $parent) {
                $operation = $definition->operation;
                $type = $this->build($definition->type, $definition);
            },


            'OperationDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $operation = $definition->operation;
                $selectionSet = $this->build($definition->selectionSet, $definition);
                $directives = $this->builds($definition->directives, $definition);
                $variableDefinitions = $this->builds($definition->variableDefinitions, $definition);
            },
            'FragmentDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $typeCondition = $this->build($definition->typeCondition, $definition);
                $selectionSet = $this->build($definition->selectionSet, $definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'FragmentSpread' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'InlineFragment' => function ($definition, $parent) {
                $typeCondition = $this->build($definition->typeCondition, $definition);
                $selectionSet = $this->build($definition->selectionSet, $definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'Name' => function ($definition, $parent) {
                return $definition->value;
            },
            'Argument' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $value = $this->build($definition->value, $definition);
            },
            'ListValue' => function ($definition, $parent) {
                $values = $this->builds($definition->values, $definition);
            },
            'SelectionSet' => function ($definition, $parent) {
                $selections = $this->builds($definition->selections, $definition);
            },
            'EnumValue' => function ($definition, $parent) {
                $value = $definition->value;
            },
            'Variable' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
            },
            'VariableDefinition' => function ($definition, $parent) {
                $variable = $this->build($definition->variable, $definition);
                $type = $this->build($definition->type, $definition);
                $defaultValue = $definition->defaultValue;
            },
            'Field' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $alias = $this->build($definition->alias, $definition);
                $selectionSet = $this->build($definition->selectionSet, $definition);
                $directives = $this->builds($definition->directives, $definition);
                $arguments = $this->builds($definition->arguments, $definition);
            },
            'Directive' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $arguments = $this->builds($definition->arguments, $definition);
            },
        ];
    }

    public function description($definition)
    {
        return isset($definition->description) ? trim($definition->description) : null;
    }

    public function buildSchema($node)
    {
        $schema = new Schema(false);
        $definitions = $this->build($node);
        foreach ($definitions as $definition) {
            if ($definition) {
                $definition->build($schema);
            }
        }
        $schema->initialize();
        return $schema;
    }

    public function build($node, $parent = null)
    {
        return $node === null ? null : call_user_func($this->builders[$node->kind], $node, $parent);
    }

    public function builds($nodes, $parent = null)
    {
        return array_map(function ($node) use (&$parent) {
            return $this->build($node, $parent);
        }, $nodes ?? []);
    }
}
