<?php

namespace Chemisus\GraphQL;

trait MutationTrait
{
    /**
     * @var Type
     */
    private $mutation;

    /**
     * @return string
     */
    public function getMutation(): string
    {
        return $this->mutation;
    }

    /**
     * @param Type $mutation
     * @return self
     */
    public function setMutation(Type $mutation): self
    {
        $this->mutation = $mutation;
        return $this;
    }
}