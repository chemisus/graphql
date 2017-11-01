<?php

namespace Chemisus\GraphQL\Builders;

use Chemisus\GraphQL\Schema;
use Chemisus\GraphQL\Type;

interface TypeBuilder
{
    /**
     * @param Schema $schema
     * @return Type
     */
    public function build(Schema $schema): Type;
}