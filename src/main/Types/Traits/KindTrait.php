<?php

namespace Chemisus\GraphQL\Types\Traits;

trait KindTrait
{
    /**
     * @var string
     */
    private $kind;

    /**
     * @return string
     */
    public function kind(): string
    {
        return $this->kind;
    }
}
