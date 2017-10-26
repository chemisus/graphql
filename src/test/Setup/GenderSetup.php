<?php

namespace GraphQL\Setup;

use GraphQL\EnumValue;
use GraphQL\Schema;

class GenderSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $gender = $schema->getType('Gender');
        $gender->addValue(new EnumValue('male'));
        $gender->addValue(new EnumValue('female'));
    }
}
