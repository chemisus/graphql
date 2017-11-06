<?php

namespace Chemisus\GraphQL;

class Document
{
    /**
     * @var Schema
     */
    public $schema;

    /**
     * @var Type[]
     */
    public $types = [];

    /**
     * @var Operation[]
     */
    public $operations = [];

    /**
     * @var Fragment[]
     */
    public $fragments = [];

    public function getOperation(?string $name = 'Query'): Operation
    {
        return $this->operations[$name];
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