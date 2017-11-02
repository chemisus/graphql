<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\EnumValue;
use Chemisus\GraphQL\Types\Field;
use Chemisus\GraphQL\Types\InputValue;

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
    public function kind(): string;

    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return string|null
     */
    public function description(): ?string;

    /**
     * @param string $name
     * @return Field
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name): Field;

    /**
     * @return Field[]|null
     */
    public function fields(): ?array;

    /**
     * @return EnumValue[]|null
     */
    public function enumValues(): ?array;

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
    public function interfaces(): ?array;

    /**
     * @return Type[]
     */
    public function possibleTypes(): array;

    /**
     * @return InputValue[]|null
     */
    public function inputFields(): ?array;

    /**
     * @return Type|null
     */
    public function ofType(): ?Type;
}
