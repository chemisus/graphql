<?php

namespace GraphQL;

interface Type
{
    /**
     * @return string
     */
    public function kind();

    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return string|null
     */
    public function description();

    /**
     * @param string $name
     * @return Field
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name);

    /**
     * @return Field[]|null
     */
    public function fields();

    /**
     * @return EnumValue[]|null
     */
    public function enumValues();

    /**
     * @param Node $node
     * @param object $parent
     * @param mixed $value
     * @param Resolver $resolver
     * @return
     */
    public function resolve(Node $node, $parent, $value, Resolver $resolver = null);

    /**
     * @param Node $node
     * @param $value
     * @return Type
     */
    public function typeOf(Node $node, $value): Type;

    /**
     * @param Node $node
     * @param mixed[] $values
     * @return string[]
     */
    public function types(Node $node, $values);

    /**
     * @return Type[]|null
     */
    public function interfaces();

    /**
     * @return Type[]|null
     */
    public function possibleTypes();

    /**
     * @return InputValue[]|null
     */
    public function inputFields();

    /**
     * @return Type|null
     */
    public function ofType();
}
