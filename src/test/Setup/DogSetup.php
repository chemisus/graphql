<?php

namespace GraphQL\Setup;

use GraphQL\Field;
use GraphQL\Schema;

class DogSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $dog = $schema->getType('Dog');
        $dog->addField(new Field($dog, 'name', $schema->getType('String')));
        $dog->addField(new Field($dog, 'guard', $schema->getType('Boolean')));
    }
}