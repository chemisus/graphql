<?php

namespace Chemisus\GraphQL;

trait DirectivesTrait
{
    /**
     * @var array
     */
    private $directives;

    /**
     * @return array
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /**
     * @param array|null $directives
     * @return self
     */
    public function setDirectives(?array $directives): self
    {
        $this->directives = $directives;
        return $this;
    }
}