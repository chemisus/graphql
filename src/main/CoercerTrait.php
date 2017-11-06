<?php

namespace Chemisus\GraphQL;

trait CoercerTrait
{
    /**
     * @var Coercer
     */
    private $coercer;

    public function coerce(Node $node, $value)
    {
        return $this->coercer !== null ? $this->coercer->coerce($node, $value) : $value;
    }

    public function setCoercer(Coercer $coercer)
    {
        $this->coercer = $coercer;
    }
}