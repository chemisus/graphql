<?php

namespace Chemisus\GraphQL;

use Exception;

trait FieldsTrait
{
    /**
     * @var Field[]
     */
    private $fields;

    public function getField(string $name): Field
    {
        if (!is_array($this->fields) || !array_key_exists($name, $this->fields)) {
            throw new Exception(sprintf("field %s.%s is undefined.", $this->getName(), $name));
        }

        return $this->fields[$name];
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array|null $fields
     * @return self
     */
    public function setFields(?array $fields): self
    {
        $this->fields = [];
        $this->putFields($fields);
        return $this;
    }

    /**
     * @param array|null $fields
     * @return self
     */
    public function putFields(?array $fields): self
    {
        foreach ($fields as $field) {
            $this->fields[$field->getName()] = $field;
        }
        return $this;
    }
}