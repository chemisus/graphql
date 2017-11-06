<?php

namespace Chemisus\GraphQL;

interface Type
{
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

//    public function setTyper(Typer $typer);

    public function coerce(Node $node, $value);

    public function resolve(Node $node, $parent, $value);
}