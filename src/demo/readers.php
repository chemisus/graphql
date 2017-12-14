<?php

namespace Chemisus\GraphQL;

class DocumentReader
{
    /**
     * @var Document
     */
    private $document;

    /**
     * @var
     */
    private $directives = [];

    /**
     * @var AbstractFactory
     */
    private $factory;

    /**
     * @var TokenStream
     */
    private $stream;

    public function __construct(TokenStream $stream, Document $document, AbstractFactory $factory)
    {
        $this->document = $document;
        $this->factory = $factory;
        $this->stream = $stream;
    }

    /**
     * @return Token|null
     */
    public function next(): ?Token
    {
        return $this->stream->next();
    }

    /**
     * @return Token
     */
    public function current(): ?Token
    {
        return $this->stream->current();
    }

    public function expectPunctuator(...$values)
    {
        return $this->stream->expectPunctuator(...$values);
    }

    public function peekPunctuator(...$values)
    {
        return $this->stream->peekPunctuator(...$values);
    }

    public function readPunctuator(...$values)
    {
        $value = $this->expectPunctuator(...$values);
        $this->next();
        return $value;
    }

    public function expectName(...$values)
    {
        return $this->stream->expectName(...$values);
    }

    public function readName(...$values)
    {
        $value = $this->expectName(...$values);
        $this->next();
        return $value;
    }

    public function peekName(...$values)
    {
        return $this->stream->peekName(...$values);
    }

    public function expectType(...$types)
    {
        return $this->stream->expectType(...func_get_args());
    }

    public function expectValue()
    {
        return $this->stream->expectValue();
    }

    public function readValue()
    {
        $value = $this->expectValue();
        $this->next();
        return $value;
    }

    public function read()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->next();
        while ($this->current()) {
            echo "NEXT  " . __FUNCTION__ . PHP_EOL;

            if ($this->peekPunctuator('@')) {
                $this->directives = $this->readDirectiveList();
            } elseif ($this->peekPunctuator('{')) {
                $this->document->addType($this->readQuery());
            } elseif ($this->peekName('query')) {
                $this->document->addType($this->readQuery());
            } elseif ($this->peekName('scalar')) {
                $this->document->addType($this->readScalarDefinition());
            } elseif ($this->peekName('type')) {
                $this->document->addType($this->readTypeDefinition());
            } elseif ($this->peekName('interface')) {
                $this->document->addType($this->readInterfaceDefinition());
            } elseif ($this->peekName('union')) {
                $this->document->addType($this->readUnionDefinition());
            } elseif ($this->peekName('schema')) {
                $this->document->addType($this->readSchemaDefinition());
            } elseif ($this->peekName('mutable')) {
            } elseif ($this->peekName('fragment')) {
            } elseif ($this->peekName('directive')) {
                $this->document->addType($this->readDirectiveDefinition());
            } else {
                var_dump($this->current());
                throw new \Exception('invalid');
            }
        }
    }

    public function flushDirectives()
    {
        $directives = $this->directives;
        $this->directives = [];
        return $directives;
    }

    public function readQuery()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readName('query');
        $name = $this->readName();
        $args = $this->readArgumentList();
        $fields = $this->readFieldList();
    }

    public function readArgument()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $name = $this->readName();
        $this->readPunctuator(':');
        $value = $this->readValue();

        return [$name, $value];
    }

    public function readArgumentList()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $args = [];

        if ($this->peekPunctuator('(')) {
            $this->next();
            do {
                $args[] = $this->readArgument();
            } while (!$this->peekPunctuator(')'));

            $this->next();
        }

        return $args;
    }

    public function readDirective()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readPunctuator('@');
        $name = $this->readName();
        $args = $this->readArgumentList();

        return new Directive($name, $args);
    }

    public function readDirectiveList()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $directives = [];

        while ($this->peekPunctuator('@')) {
            $directives[] = $this->readDirective();
        }

        return $directives;
    }

    public function readScalarDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readName('scalar');
        $name = $this->readName();

        return $this->factory->makeScalarDefinition($name, $this->flushDirectives());
    }

    public function readTypeDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readName('type');
        $name = $this->readName();
        $fields = $this->readFieldDefinitionList();

        return $this->factory->makeTypeDefinition($name, $fields, $this->flushDirectives());
    }

    public function readSchemaDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readName('schema');
        $name = $this->readName();
        $fields = $this->readFieldDefinitionList();

        return $this->factory->makeSchemaDefinition($name, $fields, $this->flushDirectives());
    }

    public function readInterfaceDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readName('interface');
        $name = $this->readName();
        $fields = $this->readFieldDefinitionList();

        return $this->factory->makeInterfaceDefinition($name, $fields, $this->flushDirectives());
    }

    public function readDirectiveDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readName('directive');
        $this->readPunctuator('@');
        $name = $this->readName();
        $args = $this->readArgumentDefinitionList();

        $this->readName('on');

        $locations = [$this->readName()];

        while ($this->peekPunctuator('|')) {
            $this->next();
            $locations[] = $this->readName();
        }

        return $this->factory->makeDirectiveDefinition($name, $args, $locations);
    }

    public function readUnionDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readName('union');
        $name = $this->readName();
        $this->readPunctuator('=');

        $types = [$this->readName()];

        while ($this->peekPunctuator('|')) {
            $this->next();
            $types[] = $this->readName();
        }

        return $this->factory->makeUnionDefinition($name, $types, $this->flushDirectives());
    }

    public function readType()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $name = $this->readName();

        return $this->factory->makeType($name);
    }

    public function readFieldDefinitionList()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->readPunctuator('{');

        $fields = [];

        do {
            $fields[] = $this->readFieldDefinition();
        } while (!$this->peekPunctuator('}'));

        $this->readPunctuator('}');

        return $fields;
    }

    public function readFieldDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $name = $this->readName();
        $args = $this->readArgumentDefinitionList();
        $this->readPunctuator(':');
        $type = $this->readType();

        return $this->factory->makeFieldDefinition($name, $type, $args, $this->flushDirectives());
    }

    public function readFieldList()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $fields = [];

        if ($this->peekPunctuator('{')) {
            $this->next();
            do {
                $fields[] = $this->readField();
            } while (!$this->current()->isPunctuator('}'));
            $this->readPunctuator('}');
        }

        return $fields;
    }

    public function readField()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $alias = null;
        $name = $this->expectName();
        $this->next();

        if ($this->peekPunctuator(':')) {
            $alias = $name;
            $this->next();
            $name = $this->expectName();
            $this->next();
        }

        $args = $this->readArgumentList();
        $fields = $this->readFieldList();

        return $this->factory->makeField($name, $alias, $args, $fields, $this->flushDirectives());
    }

    public function readArgumentDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $name = $this->readName();
        $this->readPunctuator(':');
        $type = $this->readType();

        $defaultValue = null;

        if ($this->peekPunctuator('=')) {
            $this->next();
            $defaultValue = $this->readValue();
        }

        return $this->factory->makeArgumentDefinition($name, $type, $defaultValue);
    }

    public function readArgumentDefinitionList()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $args = [];

        if ($this->peekPunctuator('(')) {
            $this->next();
            do {
                $args[] = $this->readArgumentDefinition();
            } while (!$this->peekPunctuator(')'));
            $this->next();
        }

        return $args;
    }
}
