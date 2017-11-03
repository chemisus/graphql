<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Types\Schema;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;

class GQLQueryReader
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * GQLQueryReader constructor.
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->kinds = [
            NodeKind::FIELD => function ($node) {
                return [$this->readField($node)];
            },
            NodeKind::INLINE_FRAGMENT => [$this, 'readInlineFragment'],
            NodeKind::FRAGMENT_DEFINITION => [$this, 'readFragmentDefinition'],
            NodeKind::FRAGMENT_SPREAD => [$this, 'readFragmentSpread'],
        ];
        $this->schema = $schema;
    }

    public function read(string $gql): Selection
    {
        return $this->readDocument(json_decode(json_encode(Parser::parse($gql)->toArray(true))));
    }

    public function readDocument($doc)
    {
        $fragments = array_filter($doc->definitions, function ($definition) {
            return $definition->kind = NodeKind::FRAGMENT_DEFINITION;
        });

        $operations = array_filter($doc->definitions, function ($definition) {
            return $definition->kind = NodeKind::OPERATION_DEFINITION;
        });

        $definition = $operations[0];

        $name = isset($definition->name->value) ? $definition->name->value : 'query';
        $fields = $this->readSelections($definition->selectionSet->selections);
        $query = new FieldSelection($name, $fields);

        return $query;
    }

    public function readField($node)
    {
        $name = $node->name->value;
        $alias = isset($node->alias->value) ? $node->alias->value : null;
        $fields = $this->readSelections($node->selectionSet->selections);
        $args = $this->readArgs($node->arguments);
        $on = null;
        $query = new FieldSelection($name, $fields, $alias, $on, $args);

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

    public function readFragmentDefinition($node)
    {
        return [];
    }

    public function readFragmentSpread($node)
    {
        return [];
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
