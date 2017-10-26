<?php

namespace GraphQL;

class ScalarType implements Type
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
        return 'SCALAR';
    }

    public function description()
    {
        return $this->description;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Field
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name)
    {
        throw new KindDoesNotSupportFieldsException();
    }

    public function fields()
    {
        return null;
    }

    public function resolve(Node $node, $parent, $value, Resolver $resolver = null)
    {
        return $resolver ? $resolver->resolve($node, $parent, $value) : $value;
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
