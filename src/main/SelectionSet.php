<?php

namespace Chemisus\GraphQL;

class SelectionSet implements Selection
{
    use SelectionsTrait;

    public function flatten(?Type $on = null)
    {
        return array_merge([], ...array_map(function (Selection $selection) use ($on) {
            return $selection->flatten($on);
        }, $this->getSelections()));
    }
}