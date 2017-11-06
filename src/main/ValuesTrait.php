<?php

namespace Chemisus\GraphQL;

trait ValuesTrait
{
    /**
     * @var EnumValue[]
     */
    private $values;

    public function getValue(string $name): EnumValue
    {
        return $this->value[$name];
    }

    /**
     * @return EnumValue[]
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * @param EnumValue[] $values
     * @return self
     */
    public function setValues(?array $values): self
    {
        $this->values = [];
        foreach($values as $value) {
            $this->values[$value->getName()] = $value;
        }
        return $this;
    }
}