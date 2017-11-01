<?php

namespace Chemisus\GraphQL\Setup;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Schema;

class CatSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $cat = $schema->getType('Cat');
        $cat->addField(new Field($cat, 'name', $schema->getType('String')));
        $cat->addField(new Field($cat, 'lives', $schema->getType('Integer')));
    }
}
