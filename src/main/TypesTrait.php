<?php

namespace Chemisus\GraphQL;

trait TypesTrait
{
    /**
     * @var string
     */
    private $types;

    /**
     * @return string
     */
    public function getTypes(): string
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