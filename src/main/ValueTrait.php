<?php

namespace Chemisus\GraphQL;

trait ValueTrait
{
    /**
     * @var
     */
    private $value;

    /**
     * @return
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }
}