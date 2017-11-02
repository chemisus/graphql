<?php

namespace Chemisus\GraphQL\Builders;

use Chemisus\GraphQL\Schema;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\ListType;

class ListTypeBuilder implements TypeBuilder
{
    /**
     * @var TypeBuilder
     */
    private $type;

    /**
     * @return TypeBuilder
     */
    public function getType(): TypeBuilder
    {
        return $this->type;
    }

    /**
     * @param TypeBuilder $type
     * @return self
     */
    public function setType(TypeBuilder $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function build(Schema $schema): Type
    {
        return new ListType($this->type->build($schema));
    }
}