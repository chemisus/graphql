<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Field;
use Chemisus\GraphQL\Types\Schema;
use React\Promise\ExtendedPromiseInterface;
use function React\Promise\all;

class Node
{
    /**
     * @var Field
     */
    private $field;

    /**
     * @var Selection
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
     * @var Type[]
     */
    private $types;

    /**
     * @param Schema $schema
     * @param Field $field
     * @param Selection $query
     * @param Node|null $parent
     */
    public function __construct(Schema $schema, Field $field, Selection $query, Node $parent = null)
    {
        $this->field = $field;
        $this->query = $query;
        $this->parent = $parent;
        $this->schema = $schema;
    }

    /**
     * @return Schema
     */
    public function schema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->query->name();
    }

    /**
     * @return string
     */
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
                return array_map(function (Selection $query) use ($type) {
                    return new Node($this->schema, $this->schema()->getType($type)->field($query->name()), $query, $this);
                }, $this->query->fields($type));
            }, $this->types()));
        }

        return $on === null ? $this->children : array_filter((array) $this->children, function (Node $child) use ($on) {
            return $child->field->ownerType()->name() === $on;
        });
    }

    /**
     * @return mixed[]
     */
    public function items(): ?array
    {
        return $this->items;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function arg(string $key, $default = null)
    {
        return $this->query->arg($key, $default);
    }

    /**
     * @return Node|null
     */
    public function parent(): ?Node
    {
        return $this->parent;
    }

    /**
     * @return Type[]
     */
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

    /**
     * @param mixed|null $parent
     * @param mixed|null $value
     * @return mixed
     */
    public function resolve($parent = null, $value = null)
    {
        return $this->field->resolve($this, $parent, $value);
    }
}
