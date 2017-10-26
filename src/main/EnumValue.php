<?php

namespace GraphQL;

class EnumValue
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    public function description()
    {
        return $this->description;
    }
}
