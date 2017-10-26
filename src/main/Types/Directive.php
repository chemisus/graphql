<?php

namespace GraphQL\Types;

class Directive
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    private $args;

    private $locations;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function description()
    {
        return $this->description;
    }

    public function name(): string
    {
        return $this->name;
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
