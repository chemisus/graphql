<?php

namespace Chemisus\GraphQL;

interface Selection
{
    /**
     * @param Type|null $on
     * @return FieldSelection[]
     */
    public function flatten(?Type $on = null);
}