<?php

namespace Chemisus\GraphQL;

use Countable;
use Exception;
use Throwable;

error_reporting(E_ALL);

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once 'factories.php';
require_once 'readers.php';

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
    public function read(Tokenizer $tokenizer, Document $document);
}

class TokenStream
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * @var Token
     */
    private $current;

    public function __construct(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
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

    public function peekPunctuator(...$values)
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

    public function peekName(...$values)
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
}

class Document
{
    public function addType($value)
    {
    }
}

call_user_func(function () {
    $source = <<< SCHEMA
#asdfasdf sadf asdf asdf
#asdfasdfasf
#sdfasld flaksdj flaksjdf
@asdf(anno:"asdf") 
scalar String

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

query A {
  b
  c {
    d
  }
  e:f {
    g
  }
  h:i
  h:i(e:"E")
  h:i(e:"E") {
    b
    c {
      d
    }
    e:f {
      g
    }
    h:i
    h:i(e:"E")
    h:i(e:"E") {
      a:b
    }
  }
}
SCHEMA;

    $a = <<< SCHEMA

SCHEMA;

    $document = new Document();
    $tokenizer = new Tokenizer($source);
    $factory = new ChemisusAbstractFactory();
    $stream = new TokenStream($tokenizer);
    $reader = new DocumentReader($stream, $document, $factory);
    $reader->read();


//    $option = new Option();
//
//    $option->include(ord('a'), ord('z'));
//    $option->include(ord('A'), ord('Z'));
//    $option->exclude(ord('f'), ord('h'));
//    $option->exclude(ord('o'), ord('q'));
//    var_dump($option->build());
});
