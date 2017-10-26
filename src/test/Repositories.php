<?php

namespace GraphQL;

class Repositories
{
    public static function people()
    {
        return new PeopleRepository([
            'terrence' => (object) [
                'name' => 'terrence',
                'gender' => 'male',
                'mother' => 'gwen',
            ],
            'nick' => (object) [
                'name' => 'nick',
                'gender' => 'male',
                'mother' => 'gwen',
                'father' => 'rob',
            ],
            'rob' => (object) [
                'name' => 'rob',
                'gender' => 'male',
                'mother' => 'carol',
            ],
            'jessica' => (object) [
                'name' => 'jessica',
                'gender' => 'female',
                'father' => 'mark',
                'mother' => 'sandra',
            ],
            'tom' => (object) [
                'name' => 'tom',
                'gender' => 'male',
                'father' => 'carlton',
                'mother' => 'eileen',
            ],
            'gail' => (object) [
                'name' => 'gail',
                'gender' => 'female',
                'father' => 'murial',
                'mother' => 'gilbert',
            ],
            'gwen' => (object) [
                'name' => 'gwen',
                'gender' => 'female',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'courtney' => (object) [
                'name' => 'courtney',
                'gender' => 'female',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'wade' => (object) [
                'name' => 'wade',
                'gender' => 'male',
                'father' => 'tom',
                'mother' => 'gail',
            ],
            'martin' => (object) [
                'name' => 'martin',
                'gender' => 'male',
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
                'lives' => "9",
            ],
        ]);
    }
}
