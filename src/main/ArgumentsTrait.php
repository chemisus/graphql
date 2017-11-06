<?php

namespace Chemisus\GraphQL;

trait ArgumentsTrait
{
    /**
     * @var array
     */
    private $arguments;

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array|null $arguments
     * @return self
     */
    public function setArguments(?array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }
}