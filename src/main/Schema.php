<?php

namespace Chemisus\GraphQL;

class Schema
{
    use TypesTrait;

    private $operationTypes = [];

    public function getOperation(string $operation): ObjectType
    {
        return $this->operationTypes[$operation];
    }

    public function getQuery(): ObjectType
    {
        return $this->operationTypes['query'];
    }

    public function getMutation(): ?ObjectType
    {
        return array_key_exists('mutation', $this->operationTypes) ? $this->operationTypes['mutation'] : null;
    }

    /**
     * @param OperationType[] $operationTypes
     */
    public function setOperationTypes($operationTypes)
    {
        foreach ($operationTypes as $operationType) {
            $this->operationTypes[$operationType->getOperation()] = $operationType->getType();
        }
    }
}