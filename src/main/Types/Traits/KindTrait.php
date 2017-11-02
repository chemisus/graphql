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
     * @todo uncomment type cast
     */
    public function kind() // : string
    {
        return $this->kind;
    }
}
