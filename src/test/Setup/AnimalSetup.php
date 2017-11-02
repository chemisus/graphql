<?php

namespace Chemisus\GraphQL\Setup;

use Chemisus\GraphQL\CallbackTyper;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Types\Field;
use Chemisus\GraphQL\Types\Schema;

class AnimalSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $animal = $schema->getType('Animal');

        $animal->addType($schema->getType('Dog'));
        $animal->addType($schema->getType('Cat'));

        $animal->addField(new Field($animal, 'name', $schema->getType('String')));

        $animal->typer = new CallbackTyper(function (Node $node, $value) {
            return $node->schema()->getType($value->type);
        });
    }
}
