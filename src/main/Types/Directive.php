<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class Directive
{
    use NameTrait;
    use DescriptionTrait;

    private $args;

    private $locations;

    /**
     * @param string $name
     * @param null|string $description
     */
    public function __construct(string $name, ?string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function args()
    {
        return $this->args;
    }

    public function locations()
    {
        return $this->locations;
    }
}
