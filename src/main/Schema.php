<?php

namespace Chemisus\GraphQL;

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

    public function __construct($initialize = true)
    {
        parent::__construct('Schema');

        if ($initialize) {
            $this->initialize();
        }
    }

    public function initialize()
    {
        $schema = new ObjectType('__Schema');
        $type = new ObjectType('__Type');
        $directive = new ObjectType('__Directive');
        $typeKind = new EnumType('__TypeKind');
        $field = new ObjectType('__Field');
        $enumValue = new ObjectType('__EnumValue');
        $inputValue = new ObjectType('__InputValue');
        $directiveLocation = new EnumType('__DirectiveLocation');

        if (!array_key_exists('Query', $this->types)) {
            $this->putType(new ObjectType('Query'));
        }

        $query = $this->queryType();
        $string = new ScalarType('String');
        $boolean = new ScalarType('Boolean');
        $integer = new ScalarType('Integer');
        $float = new ScalarType('Float');

        $this->putType($schema);
        $this->putType($type);
        $this->putType($directive);
        $this->putType($typeKind);
        $this->putType($field);
        $this->putType($enumValue);
        $this->putType($inputValue);
        $this->putType($directiveLocation);

        $this->putType($string);
        $this->putType($boolean);
        $this->putType($integer);

        $this->addField(new Field($this, 'query', $this->queryType()));

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
            ->setResolver(new CallbackResolver(function (Node $node) {
                return $this;
            }));

        $query->field('__type')
            ->setResolver(new CallbackResolver(function (Node $node) {
                return $this->getType($node->arg('name'));
            }));

        $string->setCoercer(new CallbackCoercer(function (Node $node, $value) {
            return (string) $value;
        }));

        $boolean->setCoercer(new CallbackCoercer(function (Node $node, $value) {
            return (bool) $value;
        }));

        $integer->setCoercer(new CallbackCoercer(function (Node $node, $value) {
            return (int) $value;
        }));

        $float->setCoercer(new CallbackCoercer(function (Node $node, $value) {
            return (float) $value;
        }));

        $schema->setCoercer(new CallbackCoercer(function (Node $node, Schema $value) {
            return (object) [
                'types' => $value->types,
                'queryType' => $value->queryType(),
                'mutationType' => $value->mutationType(),
                'directives' => $value->directives(),
            ];
        }));

        $type->setCoercer(new CallbackCoercer(function (Node $node, Type $type) {
            return (object) [
                'kind' => $type->kind(),
                'name' => $type->name(),
                'description' => $type->description(),
                'fields' => $type->fields(),
                'interfaces' => $type->interfaces(),
                'possibleTypes' => $type->possibleTypes(),
                'enumValues' => $type->enumValues(),
                'inputFields' => $type->inputFields(),
                'ofType' => $type->ofType(),
            ];
        }));

        $field->setCoercer(new CallbackCoercer(function (Node $node, Field $value) {
            return (object) [
                'name' => $value->name(),
                'description' => $value->description(),
                'args' => $value->args(),
                'type' => $value->returnType(),
                'isDeprecated' => $value->isDeprecated(),
                'deprecationReason' => $value->deprecationReason(),
            ];
        }));

        $inputValue->setCoercer(new CallbackCoercer(function (Node $node, InputValue $value) {
            return (object) [
                'name' => $value->name(),
                'description' => $value->description(),
                'type' => $value->type(),
                'defaultValue' => $value->defaultValue(),
            ];
        }));

        $enumValue->setCoercer(new CallbackCoercer(function (Node $node, EnumValue $value) {
            return (object) [
                'name' => $value->name(),
                'description' => $value->description(),
                'isDeprecated' => $value->isDeprecated(),
                'deprecationReason' => $value->deprecationReason(),
            ];
        }));

        $directive->setCoercer(new CallbackCoercer(function (Node $node, Directive $value) {
            return (object) [
                'name' => $value->name(),
                'description' => $value->description(),
                'locations' => $value->locations(),
                'args' => $value->args(),
            ];
        }));
    }

    public function queryType()
    {
        return $this->getType('Query');
    }

    public function mutationType()
    {
        return null;
    }

    public function directives()
    {
        return [];
    }

    public function putType(Type $type)
    {
        $this->types[$type->name()] = $type;
    }

    public function getType($name)
    {
        return $this->types[$name];
    }

    public function __toString()
    {
        return implode(PHP_EOL, array_filter($this->types, function (Type $type) {
            return !preg_match('/^__/', $type->name());
        }));
    }
}
