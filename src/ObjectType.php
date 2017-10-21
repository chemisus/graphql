<?php

namespace GraphQL;

class ObjectType implements Type
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Field[]
     */
    public $fields = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name)
    {
        return $this->fields[$name];
    }
}
