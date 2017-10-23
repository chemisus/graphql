<?php

namespace GraphQL\Readers;

use GraphQL\Query;
use SimpleXMLElement;

class XMLQueryReader
{
    public function read($xml): Query
    {
        return $this->build(simplexml_load_string($xml));
    }

    public function build(SimpleXMLElement $node): Query
    {
        $alias = null;
        $on = null;
        foreach ($node->attributes('graphql') as $attribute) {
            if ($attribute->getName() === 'alias') {
                $alias = (string) $attribute;
            }
            if ($attribute->getName() === 'on') {
                $on = (string) $attribute;
            }
        }

        $query = new Query($node->getName(), ...$this->buildChildren($node));
        $query->alias = $alias;
        $query->on = $on;
        $query->args = $this->buildAttributes($node);
        return $query;
    }

    public function buildAttributes(SimpleXMLElement $node)
    {
        $attributes = [];
        foreach ($node->attributes() as $attribute) {
            $attributes[$attribute->getName()] = (string) $attribute;
        }

        return $attributes;
    }

    public function buildChildren(SimpleXMLElement $node)
    {
        /**
         * @var SimpleXMLElement[] $children
         */
        $children = [];
        foreach ($node->children() as $child) {
            $children[] = $child;
        }

        return array_map([$this, 'build'], $children);
    }
}