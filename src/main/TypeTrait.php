<?php

namespace Chemisus\GraphQL;

trait TypeTrait
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @param Type $type
     * @return self
     */
    public function setType(Type $type): self
    {
        $this->type = $type;
        return $this;
    }
}