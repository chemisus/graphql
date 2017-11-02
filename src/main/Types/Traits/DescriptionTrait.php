<?php

namespace Chemisus\GraphQL\Types\Traits;

trait DescriptionTrait
{
    /**
     * @var string
     */
    private $description;

    /**
     * @return null|string
     * @todo uncomment type cast
     */
    public function description() // : ?string
    {
        return $this->description;
    }
}
