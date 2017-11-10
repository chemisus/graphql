<?php

namespace Chemisus\GraphQL;

interface Type
{
    const KIND_SCALAR = 'SCALAR';
    const KIND_OBJECT = 'OBJECT';
    const KIND_INTERFACE = 'INTERFACE';
    const KIND_UNION = 'UNION';
    const KIND_ENUM = 'ENUM';
    const KIND_INPUT_OBJECT = 'INPUT_OBJECT';
    const KIND_NON_NULL = 'NON_NULL';
    const KIND_LIST = 'LIST';

    /**
     * @return string
     */
    public function getKind(): string;

    /**
     * @return null|string
     */
    public function getName(): ?string;

    /**
     * @return null|string
     */
    public function getDescription(): ?string;

    public function getField(string $name): Field;

    public function getFields();

    public function setCoercer(Coercer $coercer);

    public function setTyper(Typer $typer);

    /**
     * @param null $on
     * @return Type[]
     */
    public function types($on = null);

    public function coerce(Node $node, $value);

    public function resolve(Node $node, $parent, $value);

    public function getFullName(): string;

    public function getBaseName(): string;

    public function getInterfaces();

    public function getPossibleTypes();

    public function getEnumValues();

    public function getOfType(): ?Type;

    public function isList(): bool;

    public function isNonNull(): bool;
}