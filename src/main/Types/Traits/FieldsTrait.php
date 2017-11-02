<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\Types\Field;

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
    public function field(string $name): Field
    {
        return $this->fields[$name];
    }

    public function fields(): ?array
    {
        return $this->fields;
    }
}
