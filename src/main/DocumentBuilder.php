<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;

class DocumentBuilder
{
    private $factories;

    private $builders;

    private $document;

    private $parsed;

    private $variables = [];

    private $source = <<< SOURCE
scalar ID
scalar String
scalar Boolean
scalar Integer
scalar Int
scalar Float

schema {
    query: Query
}

type __Schema {
    types: [__Type!]!
    queryType: __Type!
    mutationType: __Type
    directives: [__Directive!]!
}

type __Type {
    kind: __TypeKind!
    fullName: String!
    name: String
    description: String

    # OBJECT and INTERFACE only
    fields(includeDeprecated: Boolean = false): [__Field!]

    # OBJECT only
    interfaces: [__Type!]

    # INTERFACE and UNION only
    possibleTypes: [__Type!]

    # ENUM only
    enumValues(includeDeprecated: Boolean = false): [__EnumValue!]

    # INPUT_OBJECT only
    inputFields: [__InputValue!]

    # NON_NULL and LIST only
    ofType: __Type
}

type __Field {
    name: String!
    description: String
    args: [__InputValue!]!
    type: __Type!
    typeName: String!
    isDeprecated: Boolean!
    deprecationReason: String
}

type __InputValue {
    name: String!
    description: String
    type: __Type!
    defaultValue: String
}

type __EnumValue {
    name: String!
    description: String
    isDeprecated: Boolean!
    deprecationReason: String
}

enum __TypeKind {
    SCALAR
    OBJECT
    INTERFACE
    UNION
    ENUM
    INPUT_OBJECT
    LIST
    NON_NULL
}

type __Directive {
    name: String!
    description: String
    locations: [__DirectiveLocation!]!
    args: [__InputValue!]!
}

enum __DirectiveLocation {
    QUERY
    MUTATION
    FIELD
    FRAGMENT_DEFINITION
    FRAGMENT_SPREAD
    INLINE_FRAGMENT
}

SOURCE;

    public function __construct()
    {
        $this->document = new Document();

        $valueBuilder = new ValueBuilder();

        $builders = [
            NodeKind::SCALAR_TYPE_DEFINITION => new ScalarTypeBuilder(),
            NodeKind::INTERFACE_TYPE_DEFINITION => new InterfaceTypeBuilder(),
            NodeKind::OBJECT_TYPE_DEFINITION => new ObjectTypeBuilder(),
            NodeKind::ENUM_TYPE_DEFINITION => new EnumTypeBuilder(),
            NodeKind::INPUT_OBJECT_TYPE_DEFINITION => new InputObjectTypeBuilder(),
            NodeKind::UNION_TYPE_DEFINITION => new UnionTypeBuilder(),

            NodeKind::OPERATION_DEFINITION => new OperationBuilder(),
            NodeKind::FRAGMENT_DEFINITION => new FragmentBuilder(),
        ];

        $this->factories = [
            NodeKind::SCALAR_TYPE_DEFINITION => $builders[NodeKind::SCALAR_TYPE_DEFINITION],
            NodeKind::INTERFACE_TYPE_DEFINITION => $builders[NodeKind::INTERFACE_TYPE_DEFINITION],
            NodeKind::OBJECT_TYPE_DEFINITION => $builders[NodeKind::OBJECT_TYPE_DEFINITION],
            NodeKind::ENUM_TYPE_DEFINITION => $builders[NodeKind::ENUM_TYPE_DEFINITION],
            NodeKind::INPUT_OBJECT_TYPE_DEFINITION => $builders[NodeKind::INPUT_OBJECT_TYPE_DEFINITION],
            NodeKind::UNION_TYPE_DEFINITION => $builders[NodeKind::UNION_TYPE_DEFINITION],

            //=========================================================================================================

            NodeKind::OPERATION_DEFINITION => $builders[NodeKind::OPERATION_DEFINITION],
            NodeKind::FRAGMENT_DEFINITION => $builders[NodeKind::FRAGMENT_DEFINITION],
        ];

        $this->builders = [
            NodeKind::SCHEMA_DEFINITION => new SchemaBuilder(),
            NodeKind::SCALAR_TYPE_DEFINITION => $builders[NodeKind::SCALAR_TYPE_DEFINITION],
            NodeKind::INTERFACE_TYPE_DEFINITION => $builders[NodeKind::INTERFACE_TYPE_DEFINITION],
            NodeKind::OBJECT_TYPE_DEFINITION => $builders[NodeKind::OBJECT_TYPE_DEFINITION],
            NodeKind::ENUM_TYPE_DEFINITION => $builders[NodeKind::ENUM_TYPE_DEFINITION],
            NodeKind::INPUT_OBJECT_TYPE_DEFINITION => $builders[NodeKind::INPUT_OBJECT_TYPE_DEFINITION],
            NodeKind::UNION_TYPE_DEFINITION => $builders[NodeKind::UNION_TYPE_DEFINITION],

            //=========================================================================================================

            NodeKind::VARIABLE => new VariableBuilder(),
            NodeKind::ENUM_VALUE_DEFINITION => new EnumValueBuilder(),
            NodeKind::INPUT_VALUE_DEFINITION => new InputValueBuilder(),
            NodeKind::LIST_TYPE => new ListBuilder(),
            NodeKind::NON_NULL_TYPE => new NonNullBuilder(),
            NodeKind::NAMED_TYPE => new NamedTypeBuilder(),
            NodeKind::FIELD_DEFINITION => new FieldBuilder(),

            //=========================================================================================================

            NodeKind::OPERATION_TYPE_DEFINITION => new OperationTypeBuilder(),
            NodeKind::OPERATION_DEFINITION => $builders[NodeKind::OPERATION_DEFINITION],
            NodeKind::FRAGMENT_DEFINITION => $builders[NodeKind::FRAGMENT_DEFINITION],

            //=========================================================================================================

            NodeKind::NAME => $valueBuilder,
            NodeKind::FLOAT => $valueBuilder,
            NodeKind::STRING => $valueBuilder,
            NodeKind::BOOLEAN => $valueBuilder,
            NodeKind::INT => $valueBuilder,
            NodeKind::ENUM => $valueBuilder,
            NodeKind::NULL => new NullBuilder(),
            NodeKind::LST => new LstBuilder(),

            //=========================================================================================================

            NodeKind::FIELD => new FieldSelectionBuilder(),
            NodeKind::SELECTION_SET => new SelectionSetBuilder(),
            NodeKind::ARGUMENT => new ArgumentBuilder(),
            NodeKind::FRAGMENT_SPREAD => new FragmentSpreadBuilder(),
            NodeKind::INLINE_FRAGMENT => new InlineFragmentBuilder(),
        ];
    }

    public function make($node)
    {
        $maker = $this->factories[$node->kind];

        if ($maker instanceof Factory) {
            return $maker->make($this, $this->document, $node);
        }

        return call_user_func($maker, $node);
    }

    public function buildNode($node)
    {
        if ($node === null) {
            return null;
        }

        $builder = $this->builders[$node->kind];

        if ($builder instanceof Builder) {
            return $builder->build($this, $this->document, $node);
        }

        return call_user_func($builder, $node);
    }

    public function toArray($nodes)
    {
        $array = [];
        foreach ($nodes as $node) {
            $array[] = $node;
        }
        return $array;
    }

    public function buildNodes($nodes)
    {
        return array_map([$this, 'buildNode'], $this->toArray($nodes));
    }

    public function load($source)
    {
        $this->source .= PHP_EOL . $source;
        return $this;
    }

    public function build()
    {
        $this->parse();
        $this->buildSchema();
        $this->buildOperations();
        return $this->document;
    }

    public function document()
    {
        return $this->document;
    }

    public function loadVariables($variables)
    {
        foreach ($variables as $key => $value) {
            $this->document->setVariable($key, $value);
        }

        return $this;
    }

    public function parse()
    {
        if (!$this->parsed) {
            $this->parsed = Parser::parse($this->source);
        }

        return $this->parsed;
    }

    public function buildOperations()
    {
        $document = $this->parse();

        $kinds = [
            NodeKind::VARIABLE_DEFINITION,
            NodeKind::FRAGMENT_DEFINITION,
            NodeKind::OPERATION_DEFINITION,
        ];

        $nodes = $this->kinds($document, ...$kinds);

        foreach ($nodes as $node) {
            $this->make($node);
        }

        foreach ($nodes as $node) {
            $this->buildNode($node);
        }
    }

    public function buildSchema()
    {
        $parsed = $this->parse();

        $kinds = [
            NodeKind::SCALAR_TYPE_DEFINITION,
            NodeKind::INTERFACE_TYPE_DEFINITION,
            NodeKind::OBJECT_TYPE_DEFINITION,
            NodeKind::ENUM_TYPE_DEFINITION,
            NodeKind::INPUT_OBJECT_TYPE_DEFINITION,
            NodeKind::UNION_TYPE_DEFINITION,
        ];

        $nodes = $this->kinds($parsed, ...$kinds);

        foreach ($nodes as $node) {
            $this->make($node);
        }

        foreach ($this->kinds($parsed, ...$kinds) as $node) {
            $this->buildNode($node);
        }

        $kinds = [
            NodeKind::SCHEMA_DEFINITION,
        ];

        $nodes = $this->kinds($parsed, ...$kinds);

        foreach ($nodes as $node) {
            $this->buildNode($node);
        }
    }

    private function kinds($document, ...$kinds)
    {
        return array_merge([], ...array_map(function ($kind) use ($document) {
            return array_filter($this->toArray($document->definitions), function ($definition) use ($kind) {
                return $definition->kind === $kind;
            });
        }, $kinds));
    }
}