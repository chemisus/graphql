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
        return $this->getSelectionSet()->flatten($on);
    }
}