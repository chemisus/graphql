<?php

namespace GraphQL;

class Query
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $alias;

    /**
     * @var string
     */
    public $on;

    /**
     * @var array
     */
    public $args = [];

    /**
     * @var Query[]
     */
    private $fields;

    /**
     * @param string $name
     * @param Query[] $fields
     */
    public function __construct(string $name, Query... $fields)
    {
        $this->name = $name;
        $this->fields = $fields;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function alias(): string
    {
        return $this->alias ?? $this->name;
    }

    public function on(): string
    {
        return $this->on;
    }

    public function arg(string $key, $default = null)
    {
        return array_key_exists($key, $this->args) ? $this->args[$key] : $default;
    }

    public function args()
    {
        return $this->args;
    }

    /**
     * @param string $on
     * @return Query[]
     */
    public function queries(string $on)
    {
        return array_filter($this->fields, function (Query $query) use ($on) {
            return $query->on === null || $query->on === $on;
        });
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString($level = 0)
    {
        $padding = str_repeat(' ', $level * 2);
        $string = $padding;
        if ($this->alias !== null) {
            $string .= 'alias:';
        }
        $string .= $this->name;

        if (!empty($this->args)) {
            $string .= '(' . implode(', ', array_map(function ($key, $value) {
                    return $key . ':' . json_encode($value);
                }, array_keys($this->args), array_values($this->args))) . ')';
        }

        if (!empty($this->fields)) {
            $string .= ' {' . PHP_EOL . implode(PHP_EOL, array_map(function (Query $field) use ($level, $padding) {
                    return $field->toString($level + 1);
                }, $this->fields)) . PHP_EOL . $padding . '}';
        }

        return $string;
    }
}
