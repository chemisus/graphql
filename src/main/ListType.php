<?php

namespace GraphQL;

class ListType implements Type
{
    /**
     * @var Type
     */
    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function kind()
    {
        return 'LIST';
    }

    public function description()
    {
        return null;
    }

    public function name(): string
    {
        return sprintf('[%s]', $this->type->name());
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name)
    {
        return $this->type->field($name);
    }

    public function fields()
    {
        return $this->type->fields();
    }

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        $value = $resolver ? $resolver->resolve($node, $parent, $value) : $value;

        if ($value === null) {
            return null;
        }

        $array = [];

        foreach ($value as $item) {
            $array[] = $this->type->resolve($node, $parent, $item);
        }

        return $array;
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->type->typeOf($node, $value);
    }

    public function types(Node $node, $values)
    {
        return array_map(function ($value) use ($node) {
            return $this->typeOf($node, $value)->name();
        }, $values);
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
        return null;
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
