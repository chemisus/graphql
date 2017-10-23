<?php

namespace GraphQL\Setup;

use GraphQL\Field;
use GraphQL\Node;
use GraphQL\Schema;
use GraphQL\Utils\CallbackFetcher;
use GraphQL\Utils\CallbackResolver;

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