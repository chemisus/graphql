<?php

namespace Chemisus\GraphQL;

class InlineFragment implements Selection
{
    use NameTrait;
    use DirectivesTrait;
    use SelectionSetTrait;
    use TypeConditionTrait;

    public function flatten(?Type $on = null)
    {
        return $on === null || $on === $this->getTypeCondition() ? $this->getSelectionSet()->flatten($on) : [];
    }
}