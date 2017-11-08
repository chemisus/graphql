<?php

namespace Chemisus\GraphQL;

use Exception;

class Document
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var Type[]
     */
    private $types = [];

    /**
     * @var Operation[]
     */
    private $operations = [];

    /**
     * @var Fragment[]
     */
    private $fragments = [];

    private $variables = [];

    public function __construct()
    {
        $this->schema = new Schema();
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function hasType(string $name): bool
    {
        return array_key_exists($name, $this->types);
    }

    public function getType(string $name): Type
    {
        if (!$this->hasType($name)) {
            throw new Exception(sprintf("type %s is undefined", $name));
        }

        return $this->types[$name];
    }

    public function setType(string $name, Type $type): self
    {
        if ($this->hasType($name)) {
            throw new Exception(sprintf("type %s is already defined", $name));
        }

        $this->types[$name] = $type;
        return $this;
    }

    public function hasVariable(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }

    public function getVariable(string $name)
    {
        if (!$this->hasVariable($name)) {
            throw new Exception(sprintf("variable %s is undefined", $name));
        }

        return $this->variables[$name];
    }

    public function setVariable(string $name, $value): self
    {
        if ($this->hasVariable($name)) {
            throw new Exception(sprintf("variable %s is already defined", $name));
        }

        $this->variables[$name] = $value;
        return $this;
    }

    public function hasFragment(string $name): bool
    {
        return array_key_exists($name, $this->fragments);
    }

    public function getFragment(string $name): Fragment
    {
        if (!$this->hasFragment($name)) {
            throw new Exception(sprintf("fragment %s is undefined", $name));
        }

        return $this->fragments[$name];
    }

    public function setFragment(string $name, Fragment $fragment): self
    {
        if ($this->hasFragment($name)) {
            throw new Exception(sprintf("fragment %s is already defined", $name));
        }

        $this->fragments[$name] = $fragment;
        return $this;
    }

    public function hasOperation(string $name): bool
    {
        return array_key_exists($name, $this->operations);
    }

    public function getOperation(?string $name = 'Query'): Operation
    {
        if (!$this->hasOperation($name)) {
            throw new Exception(sprintf("operation %s is undefined", $name));
        }

        return $this->operations[$name];
    }

    public function setOperation(?string $name = 'Query', Operation $operation): self
    {
        if ($this->hasOperation($name)) {
            throw new Exception(sprintf("operation %s is already defined", $name));
        }

        $this->operations[$name] = $operation;
        return $this;
    }

    public function deprecate(string $type, string $name, string $reason)
    {
        $type = $this->getType($type);

        if ($type instanceof Field) {
            $type->getField($name)->setIsDeprecated(true);
        } else if ($type instanceof EnumType) {
            $type->getValue($name)->setDeprecationReason($reason);
        }
    }

    public function coercer(string $type, Coercer $coercer)
    {
        $this->types[$type]->setCoercer($coercer);
    }

    public function typer(string $type, Typer $typer)
    {
        $this->types[$type]->setTyper($typer);
    }

    public function fetcher(string $type, string $field, Fetcher $fetcher)
    {
        $this->types[$type]->getField($field)->setFetcher($fetcher);
    }

    public function resolver(string $type, string $field, Resolver $resolver)
    {
        $this->types[$type]->getField($field)->setResolver($resolver);
    }
}