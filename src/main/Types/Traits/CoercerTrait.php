<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\Coercer;

trait CoercerTrait
{
    /**
     * @var Coercer
     */
    private $coercer;

    /**
     * @param Coercer $coercer
     */
    public function setCoercer(Coercer $coercer)
    {
        $this->coercer = $coercer;
    }
}
