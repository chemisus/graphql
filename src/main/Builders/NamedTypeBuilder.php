<?php

namespace Chemisus\GraphQL\Builders;

use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\ProxyType;
use Chemisus\GraphQL\Types\Schema;

class NamedTypeBuilder implements TypeBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function build(Schema $schema): Type
    {
        return new ProxyType($schema, $this->name);
    }
}