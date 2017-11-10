<?php

namespace Chemisus\GraphQL;

class IntrospectionDocumentWirer implements Wirer
{
    public function wire(Document $document)
    {
        $document->coercer('String', new CallbackCoercer(function (Node $node, $value) {
            return $value !== null ? (string) $value : null;
        }));

        $document->coercer('Int', new CallbackCoercer(function (Node $node, $value) {
            return $value !== null ? (int) $value : null;
        }));

        $document->coercer('Boolean', new CallbackCoercer(function (Node $node, $value) {
            return $value !== null ? (bool) $value : null;
        }));

        $document->coercer('Float', new CallbackCoercer(function (Node $node, $value) {
            return $value !== null ? (float) $value : null;
        }));

        $document->resolver('Query', '__schema', new CallbackResolver(function (Node $node) use ($document) {
            return $document->getSchema();
        }));

        $document->resolver('Query', '__type', new CallbackResolver(function (Node $node) use ($document) {
            return $document->getType($node->getSelection()->getArguments()['name']);
        }));

        $document->coercer('__Schema', new CallbackCoercer(function (Node $node, Schema $value) {
            return (object) [
                'types' => [],
                'queryType' => $value->getQuery(),
                'mutationType' => $value->getMutation(),
//                'directives' => $value->getDirectives(),
//                'subscriptionType' => null,
            ];
        }));

        $document->coercer('__Type', new CallbackCoercer(function (Node $node, Type $value) {
            return (object) [
                'kind' => $value->getKind(),
                'fullName' => $value->getFullName(),
                'baseName' => $value->getBaseName(),
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'fields' => $value->getFields(),
                'interfaces' => $value->getInterfaces(),
                'possibleTypes' => $value->getPossibleTypes(),
                'enumValues' => $value->getEnumValues(),
                'ofType' => $value->getOfType(),
            ];
        }));

        $document->coercer('__Field', new CallbackCoercer(function (Node $node, Field $value) {
            return (object) [
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'args' => $value->getArguments(),
                'type' => $value->getType(),
                'typeName' => $value->getTypeName(),
                'isDeprecated' => $value->isDeprecated(),
                'deprecationReason' => $value->getDeprecationReason(),
                'directives' => $value->getDirectives(),
            ];
        }));

        $document->coercer('__InputValue', new CallbackCoercer(function (Node $node, InputValue $value) {
            return (object) [
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'type' => $value->getType(),
//                'typeName' => $value->getTypeName(),
                'defaultValue' => $value->getDefaultValue(),
            ];
        }));

        $document->coercer('__EnumValue', new CallbackCoercer(function (Node $node, EnumValue $value) {
            return (object) [
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'isDeprecated' => $value->isDeprecated(),
                'deprecationReason' => $value->getDeprecationReason(),
            ];
        }));

//        $document->coercer('__Directive', new CallbackCoercer(function (Node $node, Directive $value) {
//            return (object) [
////                'name' => $value->getName(),
////                'description' => $value->getDescription(),
////                'locations' => $value->getLocations(),
////                'args' => $value->getArguments(),
//            ];
//        }));
    }
}