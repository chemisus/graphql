<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\DescriptionTrait;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class InputValue
{
    use NameTrait;
    use DescriptionTrait;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var Type
     */
    private $type;

    /**
     * @param string $name
     * @param Type $type
     * @param mixed|null $defaultValue
     */
    public function __construct(string $name, Type $type, $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return $this->type;
    }

    /**
     * @return mixed|null
     */
    public function defaultValue()
    {
        return $this->defaultValue;
    }
}
