<?php

namespace Chemisus\GraphQL;

trait QueryTrait
{
    /**
     * @var ObjectType
     */
    private $query;

    /**
     * @return ObjectType
     */
    public function getQuery(): ObjectType
    {
        return $this->query;
    }

    /**
     * @param ObjectType $query
     * @return self
     */
    public function setQuery(ObjectType $query): self
    {
        $this->query = $query;
        return $this;
    }
}