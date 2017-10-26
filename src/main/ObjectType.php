<?php

namespace GraphQL;

class ObjectType implements FieldedType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @var string
     */
    private $description;

    /**
     * @var Coercer
     */
    private $coercer;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param Coercer $coercer
     */
    public function setCoercer(Coercer $coercer)
    {
        $this->coercer = $coercer;
    }

    public function kind()
    {
        return 'OBJECT';
    }

    public function description()
    {
        return $this->description;
    }

    public function name(): string
    {
        return $this->name;
    }

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

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        $value = $resolver ? $resolver->resolve($node, $parent, $value) : $value;

        if ($value === null) {
            return null;
        }

        $coerced = $this->coercer ? $this->coercer->coerce($node, $value) : (object) [];
        $object = (object) [];

        foreach ($node->children($this->name) as $child) {
            $name = $child->name();
            $field = isset($coerced->{$name}) ? $coerced->{$name} : (isset($value->{$name}) ? $value->{$name} : null);
            $object->{$child->alias()} = $child->resolve($value, $field);
        }

        return $object;
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this;
    }

    public function types(Node $node, $values)
    {
        return [$this->name];
    }

    public function enumValues()
    {
        return null;
    }

    public function interfaces()
    {
        return null;
    }

    public function possibleTypes()
    {
        return [$this];
    }

    public function inputFields()
    {
        return null;
    }

    public function ofType()
    {
        return null;
    }
}
