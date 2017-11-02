<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;

trait LeafTypeTrait
{
    public function type(Node $node, $value): Type
    {
        return $this;
    }

    public function possibleTypes()
    {
        return [$this];
    }
}
