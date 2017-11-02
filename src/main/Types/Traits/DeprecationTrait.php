<?php

namespace Chemisus\GraphQL\Types\Traits;

trait DeprecationTrait
{
    /**
     * @var bool
     */
    private $isDeprecated = false;

    /**
     * @var string
     */
    private $deprecationReason;

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @return string
     */
    public function deprecationReason(): ?string
    {
        return $this->deprecationReason;
    }
}
