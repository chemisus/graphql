<?php

namespace GraphQL;

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

    /**, $value = null
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
     * @var Type[]
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

    public function schema()
    {
        return $this->schema;
    }

    public function name()
    {
        return $this->query->name();
    }

    public function alias()
    {
        return $this->query->alias();
    }

    /**
     * @param string $on
     * @return Node[]
     */
    public function children(string $on)
    {
        return $this->children;
    }

    public function items()
    {
        return $this->items;
    }

    public function arg(string $key, $default = null)
    {
        return $this->query->arg($key, $default);
    }

    public function parent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return ($this->parent ? $this->parent->path() . '.' : '') . $this->field->name();
    }

    /**
     * @return Node[]
     */
    public function fetch()
    {
        $this->items = $this->field->fetch($this);

        $types = array_unique(array_map(function ($item) {
            return $this->field->returnType()->typeOf($this, $item)->name();
        }, $this->items()));

        $this->children = array_merge([], ...array_map(function (string $type) {
            return array_map(function (Query $query) use ($type) {
                return new Node($this->schema, $this->schema()->getType($type)->field($query->name()), $query, $this);
            }, $this->query->queries($type));
        }, $types));

        return count($this->items) ? array_filter($this->children, function (Node $node) {
            return $node->field->hasFetcher();
        }) : [];
    }

    public function resolve($parent = null, $value = null)
    {
        return $this->field->resolve($this, $parent, $value);
    }
}
