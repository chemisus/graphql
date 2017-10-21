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

        $query = new Query($node->getName(), ...array_map([$this, 'build'], $children));
        $query->args = $attributes;
        return $query;
    }
}