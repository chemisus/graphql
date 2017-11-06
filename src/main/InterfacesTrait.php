<?php

namespace Chemisus\GraphQL;

trait InterfacesTrait
{
    /**
     * @var InterfaceType[]
     */
    private $interfaces;

    /**
     * @return InterfaceType[]
     */
    public function getInterfaces(): ?array
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