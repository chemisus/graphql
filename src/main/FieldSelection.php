<?php

namespace Chemisus\GraphQL;

class FieldSelection implements Selection
{
    use NameTrait;
    use DirectivesTrait;
    use SelectionSetTrait;
    use TypeTrait;
    use ArgumentsTrait;

    /**
     * @var string|null
     */
    private $alias;

    public function getAlias(): string
    {
        return $this->alias ?? $this->getName();
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @param Type|null $on
     * @return FieldSelection[]
     */
    public function flatten(?Type $on = null)
    {
        return [$this];
    }

    /**
     * @param Type|null $on
     * @return FieldSelection[]
     */
    public function fields(?Type $on = null)
    {
        return $this->getSelectionSet() ? $this->getSelectionSet()->flatten($on) : [];
    }
}