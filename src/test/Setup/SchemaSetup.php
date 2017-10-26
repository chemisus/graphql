<?php

namespace GraphQL\Setup;

use GraphQL\CallbackFetcher;
use GraphQL\CallbackResolver;
use GraphQL\Field;
use GraphQL\Node;
use GraphQL\Schema;

class SchemaSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $schema->addField(new Field($schema, 'query', $schema->getType('Query')));

        $schema->field('query')->setFetcher(new CallbackFetcher(function (Node $node) {
            return [true];
        }));

        $schema->field('query')->setResolver(new CallbackResolver(function (Node $node, $parent, $value) {
            return $value;
        }));
    }
}
