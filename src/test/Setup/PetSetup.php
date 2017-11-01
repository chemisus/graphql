<?php

namespace Chemisus\GraphQL\Setup;

use Chemisus\GraphQL\CallbackTyper;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Schema;

class PetSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $pet = $schema->getType('Pet');

        $pet->addType($schema->getType('Dog'));
        $pet->addType($schema->getType('Cat'));

        $pet->typer = new CallbackTyper(function (Node $node, $value) {
            return $node->schema()->getType($value->type);
        });
    }
}
