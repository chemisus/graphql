<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\Type;

trait NullOfTypeTrait
{
    public function ofType(): ?Type
    {
        return null;
    }
}
