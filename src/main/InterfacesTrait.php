<?php

namespace Chemisus\GraphQL;

trait InterfacesTrait
{
    /**
     * @var string
     */
    private $interfaces;

    /**
     * @return string
     */
    public function getInterfaces(): string
    {
        return $this->interfaces;
    }

    /**
     * @param array|null $interfaces
     * @return self
     */
    public function setInterfaces(?array $interfaces): self
    {
        $this->interfaces = $interfaces;
        return $this;
    }
}