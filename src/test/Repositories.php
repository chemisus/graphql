<?php

namespace GraphQL;

class Repositories
{
    public static function people()
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

    public static function dogs()
    {
        return new Repository([
            'gunner' => (object) [
                'type' => 'Dog',
                'name' => 'gunner',
                'owner' => 'terrence',
                'guard' => true,
            ],
        ]);
    }

    public static function cats()
    {
        return new Repository([
            'tubs' => (object) [
                'type' => 'Cat',
                'name' => 'tubs',
                'owner' => 'martin',
                'lives' => 9,
            ],
        ]);
    }
}