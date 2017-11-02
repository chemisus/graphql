<?php

namespace Chemisus\GraphQL\Builders;

use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\NonNullType;
use Chemisus\GraphQL\Types\Schema;

class NonNullTypeBuilder implements TypeBuilder
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
        return new NonNullType($this->type->build($schema));
    }
}