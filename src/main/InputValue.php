<?php

namespace GraphQL;

class InputValue
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var Type
     */
    private $type;

    public function __construct(string $name, Type $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @return Type
     */
    public function type()
    {
        return $this->type;
    }

    public function defaultValue()
    {
        return $this->defaultValue;
    }
}
