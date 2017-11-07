<?php

namespace Chemisus\GraphQL;

use Error;
use Exception;
use React\EventLoop\LoopInterface;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\PromiseInterface;
use function React\Promise\all;

class DocumentExecutor
{
    /**
     * @var LoopInterface
     */
    private $loop;

    public function execute(Document $document, string $operation = 'Query')
    {
        /**
         * @var Node[] $roots
         * @var PromiseInterface[] $fetchers
         */
        $roots = $this->makeRootNodes($document, $document->getOperation($operation), $document->getSchema()->getOperation($document->getOperation($operation)->getOperation()));

        $value = [];
        $errors = [];

        $this->fetch($document, $roots)
            ->then(function () use (&$value, $roots, &$errors) {
                foreach ($roots as $root) {
//                    try {
                        $value[$root->getSelection()->getAlias()] = $root->resolve(null, null);
//                    } catch (Error $e) {
//                        throw $e;
//                    } catch (Exception  $e) {
//                        throw $e;
//                    }
                }
            })
            ->otherwise(function ($e) use (&$error) {
                $errors[] = $e;
            });

        $this->loop->run();

        if ($errors) {
            throw $errors[0];
        }

        return (object) $value;
    }

    public function __construct(LoopInterface $loop = null)
    {
        $this->loop = $loop ?? \React\EventLoop\Factory::create();
        Http::init($this->loop);
    }

    public function makeRootNodes(Document $document, Operation $operation, Type $type)
    {
        return array_map(function (FieldSelection $field) use ($document, $operation, $type) {
            return $this->makeRootNode($document, $type, $type->getField($field->getName()), $field);
        }, $operation->getSelectionSet()->flatten());
    }

    public function makeRootNode(Document $document, Type $type, Field $field, FieldSelection $selection)
    {
        return new Node($document, $type, $field, $selection);
    }

    public function makeChildNode(Document $document, Type $type, FieldSelection $field, Node $parent)
    {
        return new Node($document, $type, $type->getField($field->getName()), $field, $parent);
    }

    /**
     * @param Document $document
     * @param Node[] $roots
     * @return ExtendedPromiseInterface
     */
    public function fetch(Document $document, $roots): ExtendedPromiseInterface
    {
        $queue = [];

        $fetcher = function (Node $node, $parents = []) use (&$queue, &$fetcher) {
            $promise = all($parents)
                ->then(function ($parents) use ($node) {
//                    try {
                        $items = $node->fetch($parents);

                        all($items)->then(function ($items) use ($node) {
                            $node->setItems($items);
                        });
                        return $items;
//                    } catch (Error $e) {
//                        printf("ERROR %s\n", $e->getMessage());
//                        throw $e;
//                    } catch (Exception  $e) {
//                        printf("ERROR %s\n", $e->getMessage());
//                        throw $e;
//                    }
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
        /**
         * @var Node[] $queue
         */
        $queue = [$root];
        while (!empty($queue)) {
            $node = array_shift($queue);
            $types = $document->getType($node->getField()->getType()->getBaseName())->types();

            foreach ($types as $type) {
                foreach ($node->getSelection()->fields($type) as $field) {
                    $child = $this->makeChildNode($document, $type, $field, $node);
                    $node->addChild($child);
                    $queue[] = $child;
                }
            }
        }
    }
}