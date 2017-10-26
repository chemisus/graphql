<?php

namespace GraphQL\Setup;

use GraphQL\CallbackTyper;
use GraphQL\Field;
use GraphQL\Node;
use GraphQL\Schema;

class AnimalSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $animal = $schema->getType('Animal');
        $animal->addField(new Field($animal, 'name', $schema->getType('String')));

        $animal->typer = new CallbackTyper(function (Node $node, $value) {
            return $node->schema()->getType($value->type);
        });
    }
}
