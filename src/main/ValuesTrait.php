<?php

namespace Chemisus\GraphQL;

trait ValuesTrait
{
    /**
     * @var EnumValue[]
     */
    private $values;

    /**
     * @return EnumValue[]
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * @param array|null $values
     * @return self
     */
    public function setValues(?array $values): self
    {
        $this->values = $values;
        return $this;
    }
}