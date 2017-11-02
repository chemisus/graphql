<?php

namespace Chemisus\GraphQL;

interface Type
{
    const KIND_SCALAR = 'SCALAR';
    const KIND_OBJECT = 'OBJECT';
    const KIND_INTERFACE = 'INTERFACE';
    const KIND_ENUM = 'ENUM';
    const KIND_INPUT_OBJECT = 'INPUT_OBJECT';
    const KIND_LIST = 'LIST';
    const KIND_NON_NULL = 'NON_NULL';
    const KIND_UNION = 'UNION';

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
     * @return
     */
    public function resolve(Node $node, $parent, $value);

    /**
     * @param Node $node
     * @param $value
     * @return Type
     */
    public function type(Node $node, $value): Type;

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
