<?php

namespace Chemisus\GraphQL;

use Exception;
use React\Promise\FulfilledPromise;
use function React\Promise\reduce;

class StarwarsDocumentWirer
{
    public function wire(Document $document)
    {
        $document->typer('PersonPlanetInterface', new CallbackTyper(function (Node $node, $value) use ($document) {
            return $document->getType($value->type);
        }));

        $document->typer('PersonPlanetUnion', new CallbackTyper(function (Node $node, $value) use ($document) {
            return $document->getType($value->type);
        }));

        $document->coercer('Person', new CallbackCoercer(function (Node $node, $value) {
        }));

        $document->fetcher('Query', 'allPeople', new CallbackFetcher(function (Node $node)  {
            return $this->fetchPeople();
        }));

        $document->resolver('Query', 'allPeople', new CallbackResolver(function (Node $node) {
            return (object) [
                'people' => $node->getItems(),
            ];
        }));

        $document->fetcher('Query', 'allPlanets', new CallbackFetcher(function (Node $node)  {
            return $this->fetchPlanets();
        }));

        $document->resolver('Query', 'allPlanets', new CallbackResolver(function (Node $node) {
            return (object) [
                'planets' => $node->getItems(),
            ];
        }));

        $document->fetcher('Query', 'interfaces', new CallbackFetcher(function (Node $node)  {
            return reduce([
                $this->fetchPeople(),
                $this->fetchPlanets(),
            ], function ($a, $b) {
                return array_merge($a, $b);
            }, []);
        }));

        $document->resolver('Query', 'interfaces', new CallbackResolver(function (Node $node) {
            return $node->getItems();
        }));

        $document->fetcher('Query', 'interface', new CallbackFetcher(function (Node $node)  {
            $id = $node->getSelection()->getArguments()['id'];
            return $this->fetchItem($id)
                ->then(function ($item) {
                    return [$item];
                });
        }));

        $document->resolver('Query', 'interface', new CallbackResolver(function (Node $node) {
            return $node->getItems()[0];
        }));

        $document->fetcher('Query', 'union', new CallbackFetcher(function (Node $node)  {
            $id = $node->getSelection()->getArguments()['id'];
            return $this->fetchItem($id)
                ->then(function ($item) {
                    return [$item];
                });
        }));

        $document->resolver('Query', 'union', new CallbackResolver(function (Node $node) {
            return $node->getItems()[0];
        }));
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