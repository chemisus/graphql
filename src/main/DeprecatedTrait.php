<?php

namespace Chemisus\GraphQL;

trait DeprecatedTrait
{
    /**
     * @var string
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
     * @param bool $isDeprecated
     * @return self
     */
    public function setIsDeprecated(bool $isDeprecated): self
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