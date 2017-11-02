<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Schema;
use SimpleXMLElement;

class XMLQueryReader
{
    public function read(Schema $schema, $xml): Query
    {
        return $this->build($schema, simplexml_load_string($xml));
    }

    public function build(Schema $schema, SimpleXMLElement $node): Query
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

        $query = new Query($node->getName(), ...$this->buildChildren($schema, $node));
        $query->alias = $alias;
        $query->on = $on;
        $query->args = $this->buildAttributes($schema, $node);
        return $query;
    }

    public function buildAttributes(Schema $schema, SimpleXMLElement $node)
    {
        $attributes = [];
        foreach ($node->attributes() as $attribute) {
            $attributes[$attribute->getName()] = (string) $attribute;
        }

        return $attributes;
    }

    public function buildChildren(Schema $schema, SimpleXMLElement $node)
    {
        /**
         * @var SimpleXMLElement[] $children
         */
        $children = [];
        foreach ($node->children() as $child) {
            $children[] = $child;
        }

        return array_map(function (SimpleXMLElement $node) use ($schema) {
            return $this->build($schema, $node);
        }, $children);
    }
}
