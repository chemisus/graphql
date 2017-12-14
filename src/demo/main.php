<?php

namespace Chemisus\GraphQL;

use Countable;
use Exception;
use Throwable;

error_reporting(E_ALL);

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class OptionArray implements Countable
{
    /**
     * @var array
     */
    private $ranges;

    /**
     * @var bool
     */
    private $sorted;

    /**
     * @var bool
     */
    private $simplified;

    /**
     * @param array $items
     * @param bool $sorted
     * @param bool $simplified
     */
    public function __construct(array $items = [], bool $sorted = true, bool $simplified = true)
    {
        $this->ranges = $items;
        $this->sorted = $sorted;
        $this->simplified = $simplified;
    }

    public function sorted(): bool
    {
        return $this->sorted;
    }

    public function simplified(): bool
    {
        return $this->simplified;
    }

    public function count()
    {
        return count($this->ranges);
    }

    public function add(int $a, int $b)
    {
        $range = [$a, $b ?? $a];
        sort($range);
        list($min, $max) = $range;

        if (array_key_exists($min, $this->ranges)) {
            $this->ranges[$min][1] = max($this->ranges[$min][1], $max);
        } else {
            $this->ranges[$min] = $range;
        }

        $this->sorted = false;
        $this->simplified = false;
    }

    public function sort(): bool
    {
        if ($this->sorted) {
            return false;
        }

        ksort($this->ranges);
        $this->sorted = true;
        return true;
    }

    public function simplify(): bool
    {
        if ($this->simplified) {
            return false;
        }

        $this->sort();

        $ranges = [];
        $minA = null;
        $maxA = null;
        foreach ($this->ranges as $range) {
            list($minB, $maxB) = $range;

            if ($minA === null || $maxA <= $minB - 1) {
                $ranges[$minB] = $range;
                $minA = $minB;
                $maxA = $maxB;
                continue;
            }

            $maxA = max($maxA, $maxB);
            $ranges[$minA][1] = $maxA;
        }

        $this->ranges = $ranges;
        $this->simplified = true;
        return true;
    }

    public function ranges(): array
    {
        $this->simplify();
        return $this->ranges;
    }

    public function minus(OptionArray $value): OptionArray
    {
        $rangesA = $this->ranges();
        $rangesB = $value->ranges();

        $countA = count($rangesA);

        if (!$countA) {
            return new OptionArray();
        }

        $countB = count($rangesB);

        if (!$countB) {
            return new OptionArray($rangesA);
        }

        $ranges = [];

        $rangesA = array_values($rangesA);
        $rangesB = array_values($rangesB);

        $rangeA = array_shift($rangesA);
        $rangeB = array_shift($rangesB);

        while ($rangeA !== null && $rangeB !== null) {
            if ($rangeB[1] < $rangeA[0]) {
                $rangeB = array_shift($rangesB);
            }

            if ($rangeA[1] < $rangeB[0]) {
                $ranges[$rangeA[0]] = $rangeA;
                $rangeA = array_shift($rangesA);
            }

            if ($rangeA[0] < $rangeB[0]) {
                $ranges[$rangeA[0]] = [$rangeA[0], $rangeB[0] - 1];
                $rangeA[0] = $rangeB[1] + 1;
                array_unshift($rangesA, $rangeA);
            }
        }

        $ranges = array_merge($ranges, $rangesA);

        return new OptionArray($ranges);
    }

    public function __toString(): string
    {
        return implode('', array_map(function ($range) {
            return implode('-', array_map(function ($value) {
                return sprintf('\\x{%04d}', $value);
            }, array_unique($range)));
        }, $this->ranges()));
    }
}

class Option
{
    /**
     * @var OptionArray
     */
    private $includes;

    /**
     * @var OptionArray
     */
    private $excludes;

    public function __construct()
    {
        $this->includes = new OptionArray();
        $this->excludes = new OptionArray();
    }

    /**
     * @param int $a
     * @param int|null $b
     * @return Option
     */
    public function include(int $a, int $b = null): self
    {
        $this->includes->add($a, $b ?? $a);
        return $this;
    }

    /**
     * @param int $a
     * @param int|null $b
     * @return Option
     */
    public function exclude(int $a, int $b = null): self
    {
        $this->excludes->add($a, $b ?? $a);
        return $this;
    }

    public function build(): string
    {
        return (string)$this->includes->minus($this->excludes);
    }
}

class Token
{
    const TYPE_IGNORED = 'ignored';
    const TYPE_PUNCTUATOR = 'punctuator';
    const TYPE_NAME = 'name';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     * @param string $value
     * @param int $offset
     * @param int $line
     */
    public function __construct(string $type, string $value, int $offset, int $line)
    {
        $this->value = $value;
        $this->line = $line;
        $this->offset = $offset;
        $this->type = $type;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isType(string ...$types)
    {
        return in_array($this->type(), $types);
    }

    public function isIgnored()
    {
        return $this->type() === self::TYPE_IGNORED;
    }

    public function isPunctuator(string ...$values)
    {
        return $this->type() === self::TYPE_PUNCTUATOR && $this->isValue(...$values);
    }

    public function isName(string ...$values)
    {
        return $this->type() === self::TYPE_NAME && $this->isValue(...$values);
    }

    public function isInteger(...$values)
    {
        return $this->type() === self::TYPE_INTEGER && $this->isValue(...$values);
    }

    public function isFloat(...$values)
    {
        return $this->type() === self::TYPE_FLOAT && $this->isValue(...$values);
    }

    public function isString(string ...$values)
    {
        return $this->type() === self::TYPE_STRING && $this->isValue(...$values);
    }

    public function isValue(...$values)
    {
        return empty($values) || in_array($this->value(), $values, true);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function line(): int
    {
        return $this->line;
    }

    public function offset(): int
    {
        return $this->offset;
    }
}

class Tokenizer
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $line;

    public function __construct(string $source)
    {
        $ByteOrderMark = $this->unicode('FEFF');
        $HorizontalTab = $this->unicode('0009');
        $Space = $this->unicode('0020');
        $NewLine = $this->unicode('000A');
        $CarriageReturn = $this->unicode('000D');
        $FFFF = $this->unicode('FFFF');

        $CommentChar = sprintf('[%s]', implode('', [$HorizontalTab, implode('-', [$Space, $FFFF])]));
//        $CommentChar = sprintf('[^%s]', implode('', [$CarriageReturn, $NewLine]));

        $UnicodeBOM = $ByteOrderMark;
        $WhiteSpace = $HorizontalTab . $Space;
        $LineTerminator = sprintf('(%s)|[%s]', $CarriageReturn . $NewLine, implode('', [$CarriageReturn, $NewLine]));
        $Comment = sprintf('#(%s)*', $CommentChar);
        $Comma = ',';

        $Ignored = sprintf('(%s)', implode(')|(', [$UnicodeBOM, $WhiteSpace, $LineTerminator, $Comment, $Comma]));
//        $Ignored = sprintf('[%s]|(%s)', implode('', [$UnicodeBOM, $WhiteSpace, $LineTerminator, $Comma]), $Comment);

        $NegativeSign = '-';
        $NonZeroDigit = '[1-9]';
        $Digit = '[0-9]';
        $IntPart = sprintf('%s?(0)|(%s%s*)', $NegativeSign, $NonZeroDigit, $Digit);

        $Punctuator = '[!\\$():=@\\[\\]{}\\|]|(\\.\\.\\.)';
        $Name = '[_A-Za-z][_0-9A-Za-z]*';
        $IntValue = $IntPart;

        $Sign = '[+\\-]';
        $ExponentIndicator = '[eE]';
        $FractionalPart = sprintf('%s+', $Digit);
        $ExponentPart = sprintf('%s%s?%s+', $ExponentIndicator, $Sign, $Digit);

        $FloatValue = sprintf('%s(%s|%s|%s)', $IntPart, $FractionalPart . $ExponentPart, $FractionalPart, $ExponentPart);

        $Character = sprintf('[%s]', implode('', [$HorizontalTab, implode('-', [$Space, '\\[']), implode('-', ['\\]', $FFFF])]));
        $EscapedUnicode = '[0-9A-Fa-f]{4}';
        $EscapedCharacter = '["bfnrt\\/\\\\]';
        $StringCharacter = sprintf('(\\\\u%s)|(\\\\%s)|(%s)', $EscapedUnicode, $EscapedCharacter, $Character);
        $StringValue = sprintf('("")|("(%s)+?")', $StringCharacter);
//        $StringValue = sprintf('("(%s)+?")', $StringCharacter);

        $this->offset = 0;
        $this->line = 0;
        $this->source = $source;

        $this->types = [
            TOKEN::TYPE_IGNORED => $Ignored,
            TOKEN::TYPE_PUNCTUATOR => $Punctuator,
            TOKEN::TYPE_NAME => $Name,
            TOKEN::TYPE_INTEGER => $IntValue,
            TOKEN::TYPE_FLOAT => $FloatValue,
            TOKEN::TYPE_STRING => $StringValue,
        ];

        $this->pattern = sprintf('/(%s)/u', implode('|', array_map(function ($pattern, $type) {
            return sprintf('(?P<%s>%s)', $type, $pattern);
        }, $this->types, array_keys($this->types))));
    }

    function unicode($value)
    {
        return sprintf('\\x{%s}', $value);
    }

    function next(): ?Token
    {
        if ($this->offset < strlen($this->source) && preg_match($this->pattern, $this->source, $matches, PREG_OFFSET_CAPTURE, $this->offset)) {
            $values = array_intersect_key(array_filter($matches, function ($match) {
                return $match[1] !== -1;
            }), $this->types);

            $type = key($values);
            $value = current($values)[0];

            $token = new Token($type, $value, $this->offset, $this->line);

            $this->offset = $matches[0][1] + strlen($matches[0][0]);
            $this->line += count(explode(PHP_EOL, $value)) - 1;

            return $token;
        }

        return null;
    }
}

interface GraphQLAbstractFactory
{
    public function makeDirective();

    public function makeDirectiveDefinition();

    public function makeArgument();

    public function makeArgumentDefinition();

    public function makeField();

    public function makeFieldDefinition();

    public function makeType();

    public function makeTypeDefinition();

    public function makeScalarDefinition();

    public function makeInterfaceDefinition();

    public function makeUnionDefinition();

    public function makeEnumDefinition();

    public function makeEnumValue();
}

class ChemisusGraphQLAbstractFactory implements GraphQLAbstractFactory
{
    public function makeDirective()
    {
        // TODO: Implement makeDirective() method.
    }

    public function makeDirectiveDefinition()
    {
        // TODO: Implement makeDirectiveDefinition() method.
    }

    public function makeArgument()
    {
        // TODO: Implement makeArgument() method.
    }

    public function makeArgumentDefinition()
    {
        // TODO: Implement makeArgumentDefinition() method.
    }

    public function makeField()
    {
        // TODO: Implement makeField() method.
    }

    public function makeFieldDefinition()
    {
        // TODO: Implement makeFieldDefinition() method.
    }

    public function makeType()
    {
        // TODO: Implement makeType() method.
    }

    public function makeTypeDefinition()
    {
        // TODO: Implement makeTypeDefinition() method.
    }

    public function makeScalarDefinition()
    {
        // TODO: Implement makeScalarDefinition() method.
    }

    public function makeInterfaceDefinition()
    {
        // TODO: Implement makeInterfaceDefinition() method.
    }

    public function makeUnionDefinition()
    {
        // TODO: Implement makeUnionDefinition() method.
    }

    public function makeEnumDefinition()
    {
        // TODO: Implement makeEnumDefinition() method.
    }

    public function makeEnumValue()
    {
        // TODO: Implement makeEnumValue() method.
    }
}

class UnexpectedTokenException extends Exception
{
    public function __construct(Token $token, $expectedType, $expectedValue = null, $code = 0, Throwable $previous = null)
    {
        $types = $expectedType;
        $values = $expectedValue ? json_encode($expectedValue) : null;
        $message = sprintf("expected %s token. received %s: %s", implode(" ", array_filter([$types, $values])), $token->type(), $token->value());
        parent::__construct($message, $code, $previous);
    }
}

interface TokenReader
{
    public function read(Tokenizer $tokenizer, Document $docu);
}

class DocumentReader
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * @var Token
     */
    private $current;

    /**
     * @var Document
     */
    private $document;

    /**
     * @var
     */
    private $directives = [];

    /**
     * @var GraphQLAbstractFactory
     */
    private $factory;

    /**
     * @var TokenReader[]
     */
    private $readers;

    public function __construct(Tokenizer $tokenizer, Document $document)
    {
        $this->tokenizer = $tokenizer;
        $this->document = $document;
    }

    public function read()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->next();
        while ($this->current()) {
            echo "NEXT  " . __FUNCTION__ . PHP_EOL;

            if ($this->readPunctuator('@')) {
                $this->directives = $this->readDirectiveList();
            } elseif ($this->readPunctuator('{')) {
                $this->document->addType($this->readQuery());
            } elseif ($this->readName('query')) {
                $this->document->addType($this->readQuery());
            } elseif ($this->readName('scalar')) {
                $this->document->addType($this->readScalarDefinition());
            } elseif ($this->readName('type')) {
                $this->document->addType($this->readTypeDefinition());
            } elseif ($this->readName('interface')) {
                $this->document->addType($this->readInterfaceDefinition());
            } elseif ($this->readName('union')) {
                $this->document->addType($this->readUnionDefinition());
            } elseif ($this->readName('schema')) {
                $this->document->addType($this->readSchemaDefinition());
            } elseif ($this->readName('mutable')) {
            } elseif ($this->readName('fragment')) {
            } elseif ($this->readName('directive')) {
                $this->document->addType($this->readDirectiveDefinition());
            } else {
                throw new Exception('invalid');
            }
        }
    }

    public function flushDirectives()
    {
        $directives = $this->directives;
        $this->directives = [];
        return $directives;
    }

    /**
     * @return Token|null
     */
    public function next(): ?Token
    {
        do {
            $this->current = $this->tokenizer->next();
        } while ($this->current() !== null && $this->current()->isIgnored());

        if ($this->current()) {
            printf("    READ: %-12s: %s\n", $this->current()->type(), $this->current()->value());
        } else {
            echo "EOF\n";
        }

        return $this->current;
    }

    /**
     * @return Token
     */
    public function current(): ?Token
    {
        return $this->current;
    }

    public function expectPunctuator(...$values)
    {
        if (!$this->current()->isPunctuator(...$values)) {
            throw new UnexpectedTokenException($this->current(), Token::TYPE_PUNCTUATOR, $values);
        }

        return $this->current()->value();
    }

    public function readPunctuator(...$values)
    {
        return $this->current() && $this->current()->isPunctuator(...$values) ? $this->current()->value() : null;
    }

    public function expectName(...$values)
    {
        if (!$this->current()->isName(...$values)) {
            throw new UnexpectedTokenException($this->current(), Token::TYPE_NAME, $values);
        }

        return $this->current()->value();
    }

    public function readName(...$values)
    {
        return $this->current()->isName(...$values) ? $this->current()->value() : null;
    }

    public function expectType(...$types)
    {
        if (!$this->current()->isType(...$types)) {
            throw new UnexpectedTokenException($this->current(), Token::TYPE_NAME, $types);
        }

        return $this->current();
    }

    public function expectValue()
    {
        return $this->expectType(Token::TYPE_NAME, Token::TYPE_STRING, Token::TYPE_INTEGER, Token::TYPE_FLOAT);
    }

    public function readQuery()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->expectName('query');
        $this->next();

        $name = $this->expectName();
        $this->next();

        $args = $this->readArgumentList();
        $this->next();

        $fields = $this->readFieldList();
        $this->next();

        $this->next();
    }

    public function readArgument()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $name = $this->expectName();
        $this->next();
        $this->expectPunctuator(':');
        $this->next();
        $value = $this->expectValue();

        return [$name, $value];
    }

    public function readArgumentList()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $args = [];

        if ($this->readPunctuator('(')) {
            $this->next();
            do {
                $args[] = $this->readArgument();
                $this->next();
            } while (!$this->current()->isPunctuator(')'));
        }

        return $args;
    }

    public function readDirective()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->expectPunctuator('@');
        $this->next();

        $name = $this->expectName();
        $this->next();

        $args = $this->readArgumentList();

        return new Directive($name, $args);
    }

    public function readDirectiveList()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $directives = [];

        if ($this->readPunctuator('@')) {
            do {
                $directives[] = $this->readDirective();
                $this->next();
            } while ($this->current()->isPunctuator('@'));
        }

        return $directives;
    }

    public function readScalarDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->expectName('scalar');

        $this->next();
        $name = $this->expectName();

        $this->next();

        return new ScalarDefinition($name, $this->flushDirectives());
    }

    public function readTypeDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->expectName('type');
        $this->next();

        $name = $this->expectName();
        $this->next();

        $this->expectPunctuator('{');
        $this->next();

        $fields = $this->readFieldDefinitionList();
        $this->next();

        return new TypeDefinition($name, $fields, $this->flushDirectives());
    }

    public function readSchemaDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->expectName('schema');
        $this->next();

        $name = $this->expectName();
        $this->next();

        $this->expectPunctuator('{');
        $this->next();

        $fields = $this->readFieldDefinitionList();
        $this->next();

        return new SchemaDefinition($name, $fields, $this->flushDirectives());
    }

    public function readInterfaceDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->expectName('interface');
        $this->next();

        $name = $this->expectName();
        $this->next();

        $this->expectPunctuator('{');
        $this->next();

        $fields = $this->readFieldDefinitionList();
        $this->next();

        return new InterfaceDefinition($name, $fields, $this->flushDirectives());
    }

    public function readDirectiveDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->expectName('directive');
        $this->next();

        $this->expectPunctuator('@');
        $this->next();

        $name = $this->expectName();
        $this->next();

        if ($args = $this->readArgumentDefinitionList()) {
            $this->next();
        }

        $this->expectName('on');
        $this->next();

        $locations = [];

        $locations[] = $this->expectName();
        $this->next();

        while ($this->readPunctuator('|')) {
            $this->next();
            $locations[] = $this->expectName();
            $this->next();
        }

        return new DirectiveDefinition($name, $args, $locations);
    }

    public function readUnionDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $this->expectName('union');
        $this->next();

        $name = $this->expectName();
        $this->next();

        $this->expectPunctuator('=');
        $this->next();

        $types = [];

        $types[] = $this->expectName();
        $this->next();

        while ($this->readPunctuator('|')) {
            $this->next();
            $types[] = $this->expectName();
            $this->next();
        }

        return new UnionDefinition($name, $types, $this->flushDirectives());
    }

    public function readType()
    {
        return $this->expectName();
    }

    public function readFieldDefinitionList()
    {
        $fields = [];

        do {
            $fields[] = $this->readFieldDefinition();
            $this->next();
        } while (!$this->current()->isPunctuator('}'));

        return $fields;
    }

    public function readFieldDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $name = $this->expectName();
        $this->next();

        if ($args = $this->readArgumentDefinitionList()) {
            $this->next();
        }

        $this->expectPunctuator(':');
        $this->next();

        $type = $this->readType();
        $this->next();

        return new FieldDefinition($name, $type, $args, $this->flushDirectives());
    }

    public function readFieldList()
    {
        $fields = [];

        if ($this->readPunctuator('{')) {
            $this->next();
            do {
                $fields[] = $this->readField();
                $this->next();
            } while (!$this->current()->isPunctuator('}'));
        }

        return $fields;
    }

    public function readField()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $alias = null;
        $name = $this->expectName();
        $this->next();

        if ($this->readPunctuator(':')) {
            $alias = $name;
            $this->next();
            $name = $this->expectName();
            $this->next();
        }

        if ($args = $this->readArgumentList()) {
            $this->next();
        }

        if ($fields = $this->readFieldList()) {
            $this->next();
        }

        return new Field($name, $alias, $args, $fields, $this->flushDirectives());
    }

    public function readArgumentDefinition()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $name = $this->expectName();
        $this->next();
        $this->expectPunctuator(':');
        $this->next();
        $type = $this->readType();
        $this->next();

        $defaultValue = null;

        if ($this->current()->isPunctuator('=')) {
            $this->next();
            $defaultValue = $this->expectValue();
            $this->next();
        }

        return new ArgumentDefinition($name, $type, $defaultValue);
    }

    public function readArgumentDefinitionList()
    {
        echo "START " . __FUNCTION__ . PHP_EOL;

        $args = [];

        if ($this->readPunctuator('(')) {
            $this->next();
            do {
                $args[] = $this->readArgumentDefinition();
            } while (!$this->current()->isPunctuator(')'));
        }

        return $args;
    }
}

class Directive
{
    private $name;
    private $args;

    /**
     * Directive constructor.
     * @param $name
     * @param $args
     */
    public function __construct($name, $args)
    {
        $this->name = $name;
        $this->args = $args;
    }
}

class DirectiveDefinition
{
    private $name;
    private $args;
    private $locations;

    /**
     * Directive constructor.
     * @param $name
     * @param $args
     * @param $locations
     */
    public function __construct($name, $args, $locations)
    {
        $this->name = $name;
        $this->args = $args;
        $this->locations = $locations;
    }
}

class ArgumentDefinition
{
    private $name;
    private $type;
    private $defaultValue;

    /**
     * Directive constructor.
     * @param $name
     * @param $type
     * @param $defaultValue
     */
    public function __construct($name, $type, $defaultValue)
    {
        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
    }
}

class FieldDefinition
{
    private $name;
    private $type;
    private $args;
    private $directives;

    public function __construct($name, $type, $args, $directives)
    {
        $this->name = $name;
        $this->type = $type;
        $this->args = $args;
        $this->directives = $directives;
    }
}

class Field
{
    private $name;
    private $alias;
    private $args;
    private $directives;
    private $fields;

    public function __construct($name, $alias, $args, $fields, $directives)
    {
        $this->name = $name;
        $this->alias = $alias;
        $this->args = $args;
        $this->directives = $directives;
        $this->fields = $fields;
    }
}

class ScalarDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $directives;

    public function __construct(string $name, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
    }
}

class UnionDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $directives;
    private $types;

    public function __construct(string $name, $types, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
        $this->types = $types;
    }
}

class TypeDefinition
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $directives;

    private $fields;

    public function __construct(string $name, $fields, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
        $this->fields = $fields;
    }
}

class SchemaDefinition
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $directives;

    private $fields;

    public function __construct(string $name, $fields, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
        $this->fields = $fields;
    }
}

class InterfaceDefinition
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $directives;

    private $fields;

    public function __construct(string $name, $fields, $directives = [])
    {
        $this->name = $name;
        $this->directives = $directives;
        $this->fields = $fields;
    }
}


class Document
{
    public function addType($value)
    {
    }
}

call_user_func(function () {
    $source = <<< SQL
#asdfasdf sadf asdf asdf
#asdfasdfasf
#sdfasld flaksdj flaksjdf
@asdf(anno:"asdf") 
scalar String

query A {
  b
  c {
    d
  }
}

@asdf(anno:"asdf") 
@asdf(anno:"asdf") 
type asldfjalsdj {
  a(b:String):String
  a(b:String="hey"):String
}

@asdf(anno:"asdf")
@asdf(anno:"asdf") 
interface asldfjalsdj {
  a(b:String):String
  a(b:String="hey"d:String,e:String f:String):String
}

directive @asdf(value:String) on A

directive @asdf(value:String) on A | B | C

union A = B
union A = B | C

SQL;

    $document = new Document();
    $tokenizer = new Tokenizer($source);
    $reader = new DocumentReader($tokenizer, $document);
    $reader->read();


//    $option = new Option();
//
//    $option->include(ord('a'), ord('z'));
//    $option->include(ord('A'), ord('Z'));
//    $option->exclude(ord('f'), ord('h'));
//    $option->exclude(ord('o'), ord('q'));
//    var_dump($option->build());
});
