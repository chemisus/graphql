<?php

namespace Chemisus\GraphQL;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\all;

class DocumentExecutor
{
    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(LoopInterface $loop = null)
    {
        $this->loop = $loop ?? \React\EventLoop\Factory::create();
        Http::init($this->loop);
    }

    public function makeRootNodes(Document $document, Operation $operation, Type $type)
    {
        return array_map(function (FieldSelection $field) use ($document, $operation, $type) {
            return $this->makeRootNode($document, $type->getField($field->getName()), $field);
        }, $operation->getSelectionSet()->flatten());
    }

    public function makeRootNode(Document $document, Field $field, FieldSelection $selection)
    {
        return new Node($document->getSchema(), $field, $selection);
    }

    public function makeChildNode(Document $document, FieldSelection $field, Node $parent)
    {
        return new Node($document->getSchema(), $parent->getField()->getType()->getField($field->getName()), $field, $parent);
    }

    public function execute(Document $document, string $operation = 'Query')
    {
        /**
         * @var Node[] $roots
         * @var PromiseInterface[] $fetchers
         */
        $roots = $this->makeRootNodes($document, $document->getOperation($operation), $document->getSchema()->getOperation($document->getOperation($operation)->getOperation()));

        $value = [];

        $this->fetch($document, $roots)->then(function () use (&$value, $roots) {
            foreach ($roots as $root) {
                $value[$root->getSelection()->getAlias()] = $root->resolve(null, null);
            }
        });

        $this->loop->run();

        return (object) $value;
    }

    /**
     * @param Document $document
     * @param Node[] $roots
     * @return PromiseInterface
     */
    public function fetch(Document $document, $roots)
    {
        $queue = [];

        $fetcher = function (Node $node, $parents = []) use (&$queue, &$fetcher) {
            $promise = all($parents)
                ->then(function ($parents) use ($node) {
                    $items = $node->fetch($parents);
                    all($items)->then(function ($items) use ($node) {
                        $node->setItems($items);
                    });
                    return $items;
                });
            $queue[] = $promise;
            foreach ($node->getChildren() as $child) {
                $fetcher($child, $promise);
            }
        };

        foreach ($roots as $root) {
            $this->makeChildren($document, $root);

            $fetcher($root);
        }

        return all($queue);
    }

    public function makeChildren(Document $document, Node $root)
    {
        $queue = [$root];
        while (!empty($queue)) {
            $node = array_shift($queue);
            foreach ($node->getSelection()->fields() as $field) {
                $child = $this->makeChildNode($document, $field, $node);
                $node->addChild($child);
                $queue[] = $child;
            }
        }
    }
}