<?php

namespace Chemisus\GraphQL;

trait SelectionsTrait
{
    /**
     * @var Selection[]
     */
    private $selections;

    /**
     * @return Selection[]
     */
    public function getSelections()
    {
        return $this->selections;
    }

    /**
     * @param Selection[] $selections
     * @return self
     */
    public function setSelections($selections): self
    {
        $this->selections = $selections;
        return $this;
    }
}