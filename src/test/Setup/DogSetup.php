<?php

namespace Chemisus\GraphQL\Setup;

use Chemisus\GraphQL\Types\Field;
use Chemisus\GraphQL\Types\Schema;

class DogSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $dog = $schema->getType('Dog');
        $dog->addField(new Field($dog, 'name', $schema->getType('String')));
        $dog->addField(new Field($dog, 'guard', $schema->getType('Boolean')));
    }
}
