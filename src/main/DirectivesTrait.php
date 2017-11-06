<?php

namespace Chemisus\GraphQL;

trait DirectivesTrait
{
    /**
     * @var string
     */
    private $directives;

    /**
     * @return string
     */
    public function getDirectives(): string
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