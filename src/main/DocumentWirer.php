<?php

namespace Chemisus\GraphQL;

class DocumentWirer
{
    public function wire(Document $document)
    {
        $document->resolver('Query', '__type', new CallbackResolver(function (Node $node) use ($document) {
            return $document->types[$node->getSelection()->getArguments()['name']];
        }));

        $document->coercer('__Schema', new CallbackCoercer(function (Node $node, Schema $value) {
            return (object)[
                'types' => [],
                'queryType' => $value->getQuery(),
                'mutationType' => $value->getMutation(),
                'subscriptionType' => null,
                'directives' => null,
            ];
        }));

        $document->coercer('__Type', new CallbackCoercer(function (Node $node, Type $value) {
            return (object)[
                'kind' => $value->getKind(),
                'fullName' => $value->getFullName(),
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'fields' => $value->getFields(),
            ];
        }));

        $document->coercer('__Field', new CallbackCoercer(function (Node $node, Field $value) {
            return (object)[
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'type' => $value->getType(),
                'typeName' => $value->getTypeName(),
                'arguments' => $value->getArguments(),
                'directives' => $value->getDirectives(),
            ];
        }));
    }
}