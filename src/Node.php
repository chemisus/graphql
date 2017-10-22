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
     * @param Field $field
     * @param Query $query
     * @param Node|null $parent
     */
    public function __construct(Field $field, Query $query, Node $parent = null)
    {
        $this->field = $field;
        $this->query = $query;
        $this->parent = $parent;

        $this->children = array_map(function (Query $query) {
            return new Node($this->field->returnType()->field($query->name()), $query, $this);
        }, $query->queries());
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

        return count($this->items) ? array_filter($this->children, function (Node $node) {
            return is_callable($node->field->fetcher);
        }) : [];
    }

    public function resolve($parent = null, $value = null)
    {
        return $this->field->resolve($this, $parent, $value);
    }
}
