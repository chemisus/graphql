<?php

namespace Chemisus\GraphQL;

trait OperationTrait
{
    /**
     * @var string
     */
    private $operation;

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     * @return self
     */
    public function setOperation(string $operation): self
    {
        $this->operation = $operation;
        return $this;
    }
}