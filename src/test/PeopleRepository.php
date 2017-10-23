<?php

namespace GraphQL;

class PeopleRepository extends Repository
{
    public static function Sample()
    {
        return new PeopleRepository([
            'terrence' => (object) [
                'name' => 'terrence',
                'mother' => 'gwen',
            ],
            'nick' => (object) [
                'name' => 'nick',
                'mother' => 'gwen',
                'father' => 'rob',
            ],
            'rob' => (object) [
                'name' => 'rob',
                'mother' => 'carol',
            ],
            'jessica' => (object) [
                'name' => 'jessica',
                'father' => 'mark',
                'mother' => 'sandra',
            ],
            'tom' => (object) [
                'name' => 'tom',
                'father' => 'carlton',
                'mother' => 'eileen',
            ],
            'gail' => (object) [
                'name' => 'gail',
                'father' => 'murial',
                'mother' => 'gilbert',
            ],
            'gwen' => (object) [
                'name' => 'gwen',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'courtney' => (object) [
                'name' => 'courtney',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'wade' => (object) [
                'name' => 'wade',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'martin' => (object) [
                'name' => 'martin',
            ],
        ]);
    }

    public function __construct(iterable $records)
    {
        parent::__construct($records);
    }

    public function gets(...$ids)
    {
        return new PeopleRepository(parent::gets($ids));
    }

    public function filter(callable $callback)
    {
        return new PeopleRepository(parent::filter($callback));
    }

    public function fathersOf($children)
    {
        return array_values(array_filter(array_map(function ($person) {
            return $this[$person->father];
        }, $children)));
    }

    public function mothersOf($children)
    {
        return array_values(array_filter(array_map(function ($person) {
            return $this[$person->mother];
        }, $children)));
    }

    public function childrenOf($parents)
    {
        $people = $this->toArray();

        return array_filter(array_merge([], ...array_map(function ($parent) use ($people) {
            return array_values($this->filter(function ($child) use ($parent) {
                return array_key_exists('father', $child) && $child->father === $parent->name ||
                    array_key_exists('mother', $child) && $child->mother === $parent->name;
            })->toArray());
        }, $parents)));
    }
}