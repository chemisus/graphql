<?php

namespace GraphQL;

use GraphQL\Types\ObjectType;
use GraphQL\Types\Type;

class Schema extends ObjectType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type[]
     */
    private $types;

    public function __construct($name, $types = [])
    {
        parent::__construct($name);
        $this->name = $name;
        $this->types = $types;
    }

    public function putType(Type $type)
    {
        $this->types[$type->name()] = $type;
    }

    public function getType($name)
    {
        return $this->types[$name];
    }
}