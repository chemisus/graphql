<?php

namespace Chemisus\GraphQL\Wirers;

use Chemisus\GraphQL\CallbackFetcher;
use Chemisus\GraphQL\CallbackResolver;
use Chemisus\GraphQL\Document;
use Chemisus\GraphQL\Http;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Resolvers\AllFetchedItemsResolver;
use Chemisus\GraphQL\Resolvers\FirstFetchedItemResolver;
use Chemisus\GraphQL\Resolvers\LastFetchedItemResolver;
use Exception;
use React\Promise\FulfilledPromise;

class BasicDocumentWirer
{
    public function wire(Document $document)
    {
        $document->resolver('Query', 'isListTrue', new CallbackResolver(function (Node $node) {
            return [$node->getField()->getType()->isList()];
        }));

        $document->resolver('Query', 'isListFalse', new CallbackResolver(function (Node $node) {
            return $node->getField()->getType()->isList();
        }));

        $document->resolver('Query', 'isNotListTrue', new CallbackResolver(function (Node $node) {
            return [!$node->getField()->getType()->isList()];
        }));

        $document->resolver('Query', 'isNotListFalse', new CallbackResolver(function (Node $node) {
            return !$node->getField()->getType()->isList();
        }));

        $value = new CallbackResolver(function (Node $node) {
            return $node->arg('value');
        });

        $document->resolver('Query', 'string', $value);
        $document->resolver('Query', 'int', $value);
        $document->resolver('Query', 'boolean', $value);
        $document->resolver('Query', 'float', $value);
        $document->resolver('Query', 'nonNull', $value);

        $document->resolver('Query', 'testEnum', new CallbackResolver(function (Node $node) {
            $values = ['A', 'B', 'C'];
            return array_map('strtolower', array_diff($values, [$node->getSelection()->getArguments()['value']]));
        }));

        $document->fetcher('Query', 'first', new CallbackFetcher(function (Node $node) {
            return $node->arg('fetched', []);
        }));

        $document->fetcher('Query', 'last', new CallbackFetcher(function (Node $node) {
            return $node->arg('fetched', []);
        }));

        $document->fetcher('Query', 'all', new CallbackFetcher(function (Node $node) {
            return $node->arg('fetched', []);
        }));

        $document->resolver('Query', 'first', new FirstFetchedItemResolver());
        $document->resolver('Query', 'last', new LastFetchedItemResolver());
        $document->resolver('Query', 'all', new AllFetchedItemsResolver());
    }
}