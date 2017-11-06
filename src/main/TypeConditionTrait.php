<?php

namespace Chemisus\GraphQL;

trait TypeConditionTrait
{
    /**
     * @var Type
     */
    private $typeCondition;

    /**
     * @return Type
     */
    public function getTypeCondition(): Type
    {
        return $this->typeCondition;
    }

    /**
     * @param Type $typeCondition
     * @return self
     */
    public function setTypeCondition(Type $typeCondition): self
    {
        $this->typeCondition = $typeCondition;
        return $this;
    }
}