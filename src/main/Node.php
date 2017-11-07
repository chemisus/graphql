<?php

namespace Chemisus\GraphQL;

class Node
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var Field
     */
    private $field;

    /**
     * @var FieldSelection
     */
    private $selection;

    /**
     * @var Node
     */
    private $parent;

    /**
     * @var Node[]
     */
    private $children = [];

    private $items = false;

    /**
     * @var Type
     */
    private $type;

    /**
     * @param Document $document
     * @param Type $type
     * @param Field $field
     * @param FieldSelection $selection
     * @param Node $parent
     */
    public function __construct(Document $document, Type $type, Field $field, FieldSelection $selection, Node $parent = null)
    {
        $this->document = $document;
        $this->field = $field;
        $this->selection = $selection;
        $this->parent = $parent;
        $this->type = $type;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->getDocument()->getSchema();
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * @return FieldSelection
     */
    public function getSelection(): FieldSelection
    {
        return $this->selection;
    }

    /**
     * @return Node
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }

    public function addChild(Node $child)
    {
        $this->children[] = $child;
    }

    public function getChildren(Type $on = null)
    {
        return $on === null ? $this->children : array_filter($this->children, function (Node $child) use ($on) {
            return $child->getType() === $on;
        });
    }

    public function getPath(): string
    {
        $parent = $this->getParent();
        $path = $this->getField()->getName();

        while ($parent) {
            $path = sprintf('%s.%s', $parent->getField()->getName(), $path);
            $parent = $parent->getParent();
        }

        return $path;
    }

    public function fetch($parents)
    {
        return $this->getField()->fetch($this, $parents);
    }

    public function resolve($parent, $value)
    {
        return $this->getField()->resolve($this, $parent, $value);
    }
}
