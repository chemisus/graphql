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
     * @return Node[]
     */
    public function children()
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

        $this->children = array_map(function (Query $query) {
            return new Node($this->schema, $this->field->returnType()->field($query->name()), $query, $this);
        }, $this->query->queries());

        return count($this->items) ? array_filter($this->children, function (Node $node) {
            return $node->field->hasFetcher();
        }) : [];
    }

    public function resolve($parent = null, $value = null)
    {
        return $this->field->resolve($this, $parent, $value);
    }
}
