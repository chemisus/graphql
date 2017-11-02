<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Schema;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;

class GQLQueryReader
{
    public function read(Schema $schema, $gql): Query
    {
        return $this->readDocument($schema, json_decode(json_encode(Parser::parse($gql)->toArray(true))));
    }

    public function readDocument(Schema $schema, $doc)
    {
        $node = $doc->definitions[0];

        $name = isset($node->name->value) ? $node->name->value : 'query';
        $fields = $this->readSelections($schema, $node->selectionSet->selections);
        $query = new Query($name, ...$fields);

        return $query;
    }

    public function readField(Schema $schema, $node)
    {
        $name = $node->name->value;
        $alias = isset($node->alias->value) ? $node->alias->value : null;
        $fields = $this->readSelections($schema, $node->selectionSet->selections);
        $args = $this->readArgs($schema, $node->arguments);
        $query = new Query($name, ...$fields);
        $query->alias = $alias;
        $query->args = $args;

        return $query;
    }

    public function readSelections(Schema $schema, $nodes)
    {
        return array_merge([], ...array_map(function ($node) use ($schema) {
            if ($node->kind === NodeKind::FIELD) {
                return [$this->readField($schema, $node)];
            }

            if ($node->kind === NodeKind::INLINE_FRAGMENT) {
                $selections = $this->readSelections($schema, $node->selectionSet->selections);
                foreach ($selections as $selection) {
                    if ($selection->on === null) {
                        $selection->on = $node->typeCondition->name->value;
                    }
                }
                return $selections;
            }

            throw new \Exception("Unimplemented kind: " . $node->kind);
        }, (array) $nodes));
    }

    public function readArgs(Schema $schema, $nodes)
    {
        $args = [];
        foreach ($nodes as $node) {
            $args[$node->name->value] = $node->value->value;
        }
        return $args;
    }
}
