<?php

namespace GraphQL;

class Schema extends ObjectType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type[]
     */
    private $types = [];

    public function __construct($name)
    {
        parent::__construct($name);
        $this->name = $name;

        $schema = new ObjectType('__Schema');
        $type = new ObjectType('__Type');
        $directive = new ObjectType('__Directive');
        $typeKind = new EnumType('__TypeKind');
        $field = new ObjectType('__Field');
        $enumValue = new ObjectType('__EnumValue');
        $inputValue = new ObjectType('__InputValue');
        $directiveLocation = new EnumType('__DirectiveLocation');

        $query = new ObjectType('Query');
        $string = new ScalarType('String');
        $boolean = new ScalarType('Boolean');
        $integer = new ScalarType('Integer');

        $this->putType($schema);
        $this->putType($type);
        $this->putType($directive);
        $this->putType($typeKind);
        $this->putType($field);
        $this->putType($enumValue);
        $this->putType($inputValue);
        $this->putType($directiveLocation);

        $this->putType($query);
        $this->putType($string);
        $this->putType($boolean);
        $this->putType($integer);

        $schema->addField(new Field($schema, 'types', new NonNullType(new ListType(new NonNullType($type)))));
        $schema->addField(new Field($schema, 'queryType', new NonNullType($type)));
        $schema->addField(new Field($schema, 'mutationType', $type));
        $schema->addField(new Field($schema, 'directives', new NonNullType(new ListType(new NonNullType($directive)))));

        $type->addField(new Field($type, 'kind', new NonNullType($typeKind)));
        $type->addField(new Field($type, 'name', $string));
        $type->addField(new Field($type, 'description', $string));
        $type->addField(new Field($type, 'fields', new ListType(new NonNullType($field))));
        $type->addField(new Field($type, 'interfaces', new ListType(new NonNullType($type))));
        $type->addField(new Field($type, 'possibleTypes', new ListType(new NonNullType($type))));
        $type->addField(new Field($type, 'enumValues', new ListType(new NonNullType($enumValue))));
        $type->addField(new Field($type, 'inputFields', new ListType(new NonNullType($inputValue))));
        $type->addField(new Field($type, 'ofType', $type));

        $field->addField(new Field($field, 'name', new NonNullType($string)));
        $field->addField(new Field($field, 'description', $string));
        $field->addField(new Field($field, 'args', new NonNullType(new ListType(new NonNullType($inputValue)))));
        $field->addField(new Field($field, 'type', new NonNullType($type)));
        $field->addField(new Field($field, 'isDeprecated', new NonNullType($boolean)));
        $field->addField(new Field($field, 'deprecationReason', $string));

        $inputValue->addField(new Field($inputValue, 'name', new NonNullType($string)));
        $inputValue->addField(new Field($inputValue, 'description', $string));
        $inputValue->addField(new Field($inputValue, 'type', new NonNullType($type)));
        $inputValue->addField(new Field($inputValue, 'defaultValue', $string));

        $enumValue->addField(new Field($enumValue, 'name', new NonNullType($string)));
        $enumValue->addField(new Field($enumValue, 'description', $string));
        $enumValue->addField(new Field($enumValue, 'isDeprecated', new NonNullType($boolean)));
        $enumValue->addField(new Field($enumValue, 'deprecationReason', $string));

        $typeKind->addValue(new EnumValue('SCALAR'));
        $typeKind->addValue(new EnumValue('OBJECT'));
        $typeKind->addValue(new EnumValue('INTERFACE'));
        $typeKind->addValue(new EnumValue('UNION'));
        $typeKind->addValue(new EnumValue('ENUM'));
        $typeKind->addValue(new EnumValue('INPUT_OBJECT'));
        $typeKind->addValue(new EnumValue('LIST'));
        $typeKind->addValue(new EnumValue('NON_NULL'));

        $directive->addField(new Field($directive, 'name', new NonNullType($string)));
        $directive->addField(new Field($directive, 'description', $string));
        $directive->addField(new Field($directive, 'locations', new NonNullType(new ListType(new NonNullType($directiveLocation)))));
        $directive->addField(new Field($directive, 'args', new NonNullType(new ListType(new NonNullType($inputValue)))));

        $directiveLocation->addValue(new EnumValue('QUERY'));
        $directiveLocation->addValue(new EnumValue('MUTATION'));
        $directiveLocation->addValue(new EnumValue('FIELD'));
        $directiveLocation->addValue(new EnumValue('FRAGMENT_DEFINITION'));
        $directiveLocation->addValue(new EnumValue('FRAGMENT_SPREAD'));
        $directiveLocation->addValue(new EnumValue('INLINE_FRAGMENT'));

        $query->addField(new Field($query, '__schema', new NonNullType($schema)));
        $query->addField(new Field($query, '__type', new NonNullType($type)));

        $query->field('__schema')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return [$this];
            }))
            ->setResolver(new CallbackResolver(function (Node $node) {
                return $this;
            }));

        $query->field('__type')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return [$this->getType($node->arg('name'))];
            }))
            ->setResolver(new CallbackResolver(function (Node $node) {
                return $this->getType($node->arg('name'));
            }));

        $schema->field('queryType')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return [$this->queryType()];
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Schema $schema) {
                return $this->queryType();
            }));

        $schema->field('mutationType')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return [];
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Schema $schema) {
                return null;
            }));

        $schema->field('directives')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return [];
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Schema $schema) {
                return [];
            }));

        $type->field('kind')
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->kind();
            }));

        $type->field('name')
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->name();
            }));

        $type->field('description')
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->description();
            }));

        $type->field('fields')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return array_merge([], ...array_map(function (Type $type) {
                    return array_values((array) $type->fields());
                }, $node->parent()->items()));
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->fields();
            }));

        $type->field('interfaces')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return array_merge([], ...array_map(function (Type $type) {
                    return array_values((array) $type->interfaces());
                }, $node->parent()->items()));
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->interfaces();
            }));

        $type->field('possibleTypes')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return array_merge([], ...array_map(function (Type $type) {
                    return array_values((array) $type->possibleTypes());
                }, $node->parent()->items()));
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->possibleTypes();
            }));

        $type->field('enumValues')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return array_merge([], ...array_map(function (Type $type) {
                    return array_values((array) $type->enumValues());
                }, $node->parent()->items()));
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->enumValues();
            }));

        $type->field('inputFields')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return array_merge([], ...array_map(function (Type $type) {
                    return array_values((array) $type->inputFields());
                }, $node->parent()->items()));
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->inputFields();
            }));

        $type->field('ofType')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return array_map(function (Type $type) {
                    return $type->ofType();
                }, $node->parent()->items());
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Type $parent, $value) {
                return $parent->ofType();
            }));

        $field->field('name')
            ->setResolver(new CallbackResolver(function (Node $node, Field $parent, $value) {
                return $parent->name();
            }));

        $field->field('description')
            ->setResolver(new CallbackResolver(function (Node $node, Field $parent, $value) {
                return $parent->description();
            }));

        $field->field('args')
            ->setResolver(new CallbackResolver(function (Node $node, Field $parent, $value) {
                return $parent->args();
            }));

        $field->field('type')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return array_map(function (Field $field) {
                    return $field->returnType();
                }, $node->parent()->items());
            }))
            ->setResolver(new CallbackResolver(function (Node $node, Field $parent, $value) {
                return $parent->returnType();
            }));

        $field->field('isDeprecated')
            ->setResolver(new CallbackResolver(function (Node $node, Field $parent, $value) {
                return $parent->isDeprecated();
            }));

        $field->field('deprecationReason')
            ->setResolver(new CallbackResolver(function (Node $node, Field $parent, $value) {
                return $parent->deprecationReason();
            }));

        $inputValue->field('name')
            ->setResolver(new CallbackResolver(function (Node $node, InputValue $parent, $value) {
                return $parent->name();
            }));

        $inputValue->field('description')
            ->setResolver(new CallbackResolver(function (Node $node, InputValue $parent, $value) {
                return $parent->description();
            }));

        $inputValue->field('type')
            ->setFetcher(new CallbackFetcher(function (Node $node) {
                return array_map(function (Field $field) {
                    return $field->returnType();
                }, $node->parent()->items());
            }))
            ->setResolver(new CallbackResolver(function (Node $node, InputValue $parent, $value) {
                return $parent->type();
            }));

        $inputValue->field('defaultValue')
            ->setResolver(new CallbackResolver(function (Node $node, InputValue $parent, $value) {
                return $parent->defaultValue();
            }));

        $enumValue->field('name')
            ->setResolver(new CallbackResolver(function (Node $node, EnumValue $parent, $value) {
                return $parent->name();
            }));

        $enumValue->field('description')
            ->setResolver(new CallbackResolver(function (Node $node, EnumValue $parent, $value) {
                return $parent->description();
            }));

        $enumValue->field('isDeprecated')
            ->setResolver(new CallbackResolver(function (Node $node, EnumValue $parent, $value) {
                return $parent->isDeprecated();
            }));

        $enumValue->field('deprecationReason')
            ->setResolver(new CallbackResolver(function (Node $node, EnumValue $parent, $value) {
                return $parent->deprecationReason();
            }));

        $directive->field('name')
            ->setResolver(new CallbackResolver(function (Node $node, Directive $parent, $value) {
                return $parent->name();
            }));

        $directive->field('description')
            ->setResolver(new CallbackResolver(function (Node $node, Directive $parent, $value) {
                return $parent->description();
            }));

        $directive->field('locations')
            ->setResolver(new CallbackResolver(function (Node $node, Directive $parent, $value) {
                return $parent->description();
            }));

        $directive->field('args')
            ->setResolver(new CallbackResolver(function (Node $node, Directive $parent, $value) {
                return $parent->args();
            }));
    }

    public function queryType()
    {
        return $this->getType('Query');
    }

    public function putType(Type $type)
    {
        $this->types[$type->name()] = $type;
    }

    public function getType($name)
    {
        return $this->types[$name];
    }
}
