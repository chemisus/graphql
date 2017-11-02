<?php

namespace Chemisus\GraphQL;

use React\Promise\ExtendedPromiseInterface;
use function React\Promise\all;

class Node
{
    /**
     * @var Field
     */
    private $field;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var mixed[]
     */
    public $items;

    /**
     * @var Node|null
     */
    private $parent;

    /**
     * @var Node[]
     */
    private $children;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var
     */
    private $types;

    /**
     * @param Schema $schema
     * @param Field $field
     * @param Query $query
     * @param Node|null $parent
     */
    public function __construct(Schema $schema, Field $field, Query $query, Node $parent = null)
    {
        $this->field = $field;
        $this->query = $query;
        $this->parent = $parent;
        $this->schema = $schema;
    }

    public function schema(): Schema
    {
        return $this->schema;
    }

    public function name(): string
    {
        return $this->query->name();
    }

    public function alias(): string
    {
        return $this->query->alias();
    }

    /**
     * @param string $on
     * @return Node[]
     */
    public function children(string $on = null): array
    {
        if ($this->children === null) {
            $this->children = array_merge([], ...array_map(function (string $type) {
                return array_map(function (Query $query) use ($type) {
                    return new Node($this->schema, $this->schema()->getType($type)->field($query->name()), $query, $this);
                }, $this->query->queries($type));
            }, $this->types()));
        }

        return $on === null ? $this->children : array_filter((array) $this->children, function (Node $child) use ($on) {
            return $child->field->ownerType()->name() === $on;
        });
    }

    public function items(): ?array
    {
        return $this->items;
    }

    public function arg(string $key, $default = null)
    {
        return $this->query->arg($key, $default);
    }

    public function parent(): ?Node
    {
        return $this->parent;
    }

    public function types(): array
    {
        if ($this->types === null) {
            $this->types = array_unique(array_values(array_map(function (Type $type) {
                return $type->name();
            }, $this->field->returnType()->possibleTypes())));
        }

        return $this->types;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return ($this->parent ? $this->parent->path() . '.' : '') . $this->field->name();
    }

    /**
     * @return ExtendedPromiseInterface
     */
    public function fetch(): ExtendedPromiseInterface
    {
        return all($this->field->fetch($this))->then(function ($items) {
            $this->items = $items;

            return $this->children();
        });
    }

    public function resolve($parent = null, $value = null)
    {
        return $this->field->resolve($this, $parent, $value);
    }
}
