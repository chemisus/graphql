<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Types\Traits\DeprecationTrait;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class EnumValue
{
    use NameTrait;
    use DescriptionTrait;
    use DeprecationTrait;

    public function __construct(string $name, ?string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }
}
