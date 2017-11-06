<?php

namespace Chemisus\GraphQL;

trait ValuesTrait
{
    /**
     * @var string
     */
    private $values;

    /**
     * @return string
     */
    public function getValues(): string
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