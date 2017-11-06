<?php

namespace Chemisus\GraphQL;

trait DeprecatedTrait
{
    /**
     * @var string
     */
    private $isDeprecated;

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
     * @param string|null $isDeprecated
     * @return self
     */
    public function setIsDeprecated(?string $isDeprecated): self
    {
        $this->isDeprecated = $isDeprecated;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason;
    }

    /**
     * @param string|null $deprecationReason
     * @return self
     */
    public function setDeprecationReason(?string $deprecationReason): self
    {
        $this->deprecationReason = $deprecationReason;
        return $this;
    }
}