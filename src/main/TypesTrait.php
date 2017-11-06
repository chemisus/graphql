<?php

namespace Chemisus\GraphQL;

trait TypesTrait
{
    /**
     * @var array
     */
    private $types;

    /**
     * @return array
     */
    public function getTypes(): ?array
    {
        return $this->types;
    }

    /**
     * @param array|null $types
     * @return self
     */
    public function setTypes(?array $types): self
    {
        $this->types = $types;
        return $this;
    }
}