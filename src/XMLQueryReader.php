<?php

namespace GraphQL;

use SimpleXMLElement;

class XMLQueryReader
{
    public function read($xml): Query
    {
        return $this->build(simplexml_load_string($xml));
    }

    public function build(SimpleXMLElement $node): Query
    {
        /**
         * @var SimpleXMLElement[] $children
         */
        $children = [];
        foreach ($node->children() as $child) {
            $children[] = $child;
        }

        $attributes = [];
        foreach ($node->attributes() as $attribute) {
            $attributes[$attribute->getName()] = (string) $attribute;
        }

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

        $query = new Query($node->getName(), ...array_map([$this, 'build'], $children));
        $query->alias = $alias;
        $query->on = $on;
        $query->args = $attributes;
        return $query;
    }
}