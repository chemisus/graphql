<?php

namespace Chemisus\GraphQL;

class DocumentWirer
{
    public function wire(Document $document)
    {
        $graph = [];

//        $document->coercer('Query', new CallbackCoercer(function (Node $node, $value) {
//            return (object) [];
//        }));
//
//        $document->fetcher('Query', 'human', new CallbackFetcher(function (Node $node) use (&$graph) {
//            $id = $node->getSelection()->getArguments()['id'];
//            printf("\nSTART %s %s", $node->getPath(), $id);
//            return Http::get(sprintf('https://swapi.co/api/people/%s/', $id))
//                ->then(function ($data) use ($node, $id, &$graph) {
//                    printf("\nFINISH %s %s", $node->getPath(), $id);
//                    return [$graph[$id] = json_decode($data)];
//                });
//        }));
//
//        $document->resolver('Query', 'human', new CallbackResolver(function (Node $node) use (&$graph) {
//            return $graph[$node->getSelection()->getArguments()['id']];
//        }));
//
//        $document->coercer('Human', new CallbackCoercer(function (Node $node, $value) use (&$graph) {
//            return (object) [
//                'appearsIn' => $value->films,
//                'starships' => $value->starships,
//            ];
//        }));
    }
}