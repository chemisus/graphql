<?php

namespace Chemisus\GraphQL\Setup;

use Chemisus\GraphQL\EnumValue;
use Chemisus\GraphQL\Schema;

class GenderSetup
{
    public function setup(Schema $schema, &$graph)
    {
        $gender = $schema->getType('Gender');
        $gender->addValue(new EnumValue('male'));
        $gender->addValue(new EnumValue('female'));
    }
}
