<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Schema;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;

class GQLQueryReader
{
    public function __construct()
    {
        $this->kinds = [
            NodeKind::FIELD => function ($node) {
                return [$this->readField($node)];
            },
            NodeKind::INLINE_FRAGMENT => [$this, 'readInlineFragment'],
        ];
    }

    public function read(Schema $schema, $gql): Query
    {
        return $this->readDocument(json_decode(json_encode(Parser::parse($gql)->toArray(true))));
    }

    public function readDocument($doc)
    {
        $node = $doc->definitions[0];

        $name = isset($node->name->value) ? $node->name->value : 'query';
        $fields = $this->readSelections($node->selectionSet->selections);
        $query = new Query($name, $fields);

        return $query;
    }

    public function readField($node)
    {
        $name = $node->name->value;
        $alias = isset($node->alias->value) ? $node->alias->value : null;
        $fields = $this->readSelections($node->selectionSet->selections);
        $args = $this->readArgs($node->arguments);
        $on = null;
        $query = new Query($name, $fields, $alias, $on, $args);

        return $query;
    }

    public function readInlineFragment($node)
    {
        $selections = $this->readSelections($node->selectionSet->selections);
        foreach ($selections as $selection) {
            if ($selection->on === null) {
                $selection->on = $node->typeCondition->name->value;
            }
        }
        return $selections;
    }

    public function readSelections($nodes)
    {
        return array_merge([], ...array_map(function ($node) {
            return call_user_func($this->kinds[$node->kind], $node);
        }, (array) $nodes));
    }

    public function readArgs($nodes)
    {
        $args = [];
        foreach ($nodes as $node) {
            $args[$node->name->value] = $node->value->value;
        }
        return $args;
    }
}
