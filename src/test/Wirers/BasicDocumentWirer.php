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


    public function fetchItem($ref)
    {
        list($type, $id) = explode('/', $ref);
        switch ($type) {
            case 'people':
                return $this->fetchPerson($id);
            case 'planets':
                return $this->fetchPlanet($id);
            default:
                throw new Exception(sprintf("%s is not a valid reference", $ref));
        }
    }

    public function fetchPerson($id)
    {
        return $this->fetchURL('https://swapi.co/api/people/' . $id . '/')
            ->then(function ($person) {
                $person->type = 'Person';
                return $person;
            });
    }

    public function fetchPlanet($id)
    {
        return $this->fetchURL('https://swapi.co/api/planets/' . $id . '/')
            ->then(function ($planet) {
                $planet->type = 'Planet';
                return $planet;
            });
    }

    public function fetchPeople()
    {
        return $this->fetchURL('https://swapi.co/api/people/')
            ->then(function ($response) {
                return array_map(function ($person) {
                    $person->type = 'Person';
                    return $person;
                }, $response->results);
            });
    }

    public function fetchPlanets()
    {
        return $this->fetchURL('https://swapi.co/api/planets/')
            ->then(function ($response) {
                return array_map(function ($planet) {
                    $planet->type = 'Planet';
                    return $planet;
                }, $response->results);
            });
    }

    public function fetchURL(string $url)
    {
        $dir = dirname(dirname(__DIR__)) . '/out/cache/';
        $key = base64_encode($url);
        $file = $dir . $key;

        if (file_exists($file)) {
            return new FulfilledPromise(json_decode(file_get_contents($file)));
        }

        return Http::get($url)
            ->then(function ($data) use ($dir, $file) {
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($file, $data);
                return json_decode($data);
            });
    }
}