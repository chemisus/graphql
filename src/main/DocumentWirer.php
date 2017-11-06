<?php

namespace Chemisus\GraphQL;

use GraphQL\Type\Definition\DirectiveLocation;
use GraphQL\Type\TypeKind;

class DocumentWirer
{
    public function wire(Document $document)
    {
        $document->resolver('Query', '__type', new CallbackResolver(function (Node $node) use ($document) {
            return $document->getType($node->getSelection()->getArguments()['name']);
        }));


// type __Schema {
//     types: [__Type!]!
//     queryType: __Type!
//     mutationType: __Type
//     directives: [__Directive!]!
// }
        $document->coercer('__Schema', new CallbackCoercer(function (Node $node, Schema $value) {
            return (object) [
                'types' => [],
                'queryType' => $value->getQuery(),
                'mutationType' => $value->getMutation(),
                'directives' => null,
                'subscriptionType' => null,
            ];
        }));

// type __Type {
//     kind: __TypeKind!
//     fullName: String!
//     name: String
//     description: String
//
//     # OBJECT and INTERFACE only
//     fields(includeDeprecated: Boolean = false): [__Field!]
//
//     # OBJECT only
//     interfaces: [__Type!]
//
//     # INTERFACE and UNION only
//     possibleTypes: [__Type!]
//
//     # ENUM only
//     enumValues(includeDeprecated: Boolean = false): [__EnumValue!]
//
//     # INPUT_OBJECT only
//     inputFields: [__InputValue!]
//
//     # NON_NULL and LIST only
//     ofType: __Type
// }
//
        $document->coercer('__Type', new CallbackCoercer(function (Node $node, Type $value) {
            return (object) [
                'kind' => $value->getKind(),
                'fullName' => $value->getFullName(),
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'fields' => $value->getFields(),
//                'interfaces' => $value->getInterfaces(),
                'possibleTypes' => $value->getPossibleTypes(),
                'enumValues' => $value->getEnumValues(),
//                'ofType' => $value->getOfType(),
            ];
        }));

// type __Field {
//     name: String!
//     description: String
//     args: [__InputValue!]!
//     type: __Type!
//     typeName: String!
//     isDeprecated: Boolean!
//     deprecationReason: String
// }
//
        $document->coercer('__Field', new CallbackCoercer(function (Node $node, Field $value) {
            return (object) [
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'type' => $value->getType(),
                'typeName' => $value->getTypeName(),
                'arguments' => $value->getArguments(),
                'directives' => $value->getDirectives(),
            ];
        }));

// type __InputValue {
//     name: String!
//     description: String
//     type: __Type!
//     defaultValue: String
// }
//
        $document->coercer('__InputValue', new CallbackCoercer(function (Node $node, InputValue $value) {
            return (object) [
            ];
        }));

// type __EnumValue {
//     name: String!
//     description: String
//     isDeprecated: Boolean!
//     deprecationReason: String
// }
        $document->coercer('__EnumValue', new CallbackCoercer(function (Node $node, EnumValue $value) {
            return (object) [
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'isDeprecated' => $value->isDeprecated(),
                'deprecationReason' => $value->getDeprecationReason(),
            ];
        }));

// type __Directive {
//     name: String!
//     description: String
//     locations: [__DirectiveLocation!]!
//     args: [__InputValue!]!
// }
        $document->coercer('__Directive', new CallbackCoercer(function (Node $node, $value) {
            return (object) [
            ];
        }));
    }
}