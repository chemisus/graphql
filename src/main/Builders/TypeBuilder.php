<?php

namespace Chemisus\GraphQL\Builders;

use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Schema;

interface TypeBuilder
{
    /**
     * @param Schema $schema
     * @return Type
     */
    public function build(Schema $schema): Type;
}