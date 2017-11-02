<?php

namespace Chemisus\GraphQL\Types\Traits;

trait NameTrait
{
    /**
     * @var string
     */
    private $name;

    /**
     * @return null|string
     */
    public function name(): ?string
    {
        return $this->name;
    }
}
