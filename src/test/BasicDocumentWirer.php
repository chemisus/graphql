<?php

namespace Chemisus\GraphQL;

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
            return $node->getSelection()->getArguments()['value'];
        });

        $document->resolver('Query', 'string', $value);
        $document->resolver('Query', 'int', $value);
        $document->resolver('Query', 'boolean', $value);
        $document->resolver('Query', 'float', $value);
        $document->resolver('Query', 'nonNull', $value);
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