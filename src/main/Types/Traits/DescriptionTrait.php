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
     */
    public function description(): ?string
    {
        return $this->description;
    }
}
