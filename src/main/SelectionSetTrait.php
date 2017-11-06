<?php

namespace Chemisus\GraphQL;

trait SelectionSetTrait
{
    /**
     * @var SelectionSet|null
     */
    private $selectionSet;

    /**
     * @return SelectionSet|null
     */
    public function getSelectionSet(): ?SelectionSet
    {
        return $this->selectionSet;
    }

    /**
     * @param SelectionSet|null $selectionSet
     * @return self
     */
    public function setSelectionSet(?SelectionSet $selectionSet): self
    {
        $this->selectionSet = $selectionSet;
        return $this;
    }
}