<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\Field;

trait FieldsTrait
{
    /**
     * @var Field[]
     */
    public $fields = [];

    public function addField(Field $field)
    {
        $this->fields[$field->name()] = $field;
        return $this;
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name)
    {
        return $this->fields[$name];
    }

    public function fields()
    {
        return $this->fields;
    }
}
