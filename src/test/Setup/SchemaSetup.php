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
    }
}
