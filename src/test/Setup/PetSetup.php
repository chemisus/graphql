<?php

namespace GraphQL\Setup;

use GraphQL\CallbackTyper;
use GraphQL\Node;
use GraphQL\Schema;

class PetSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $pet = $schema->getType('Pet');

        $pet->typer = new CallbackTyper(function (Node $node, $value) {
            return $node->schema()->getType($value->type);
        });
    }
}