<?php

namespace Chemisus\GraphQL;

class Query
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    public $on;

    /**
     * @var array
     */
    private $args = [];

    /**
     * @var Query[]
     */
    private $fields;

    /**
     * @param string $name
     * @param Query[] $fields
     * @param null|string $alias
     * @param null|string $on
     * @param array|null $args
     */
    public function __construct(string $name, array $fields, ?string $alias = null, ?string $on = null, ?array $args = null)
    {
        $this->name = $name;
        $this->fields = $fields;
        $this->on = $on;
        $this->args = $args;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function alias(): string
    {
        return $this->alias ?? $this->name;
    }

    /**
     * @return string|null
     */
    public function on(): ?string
    {
        return $this->on;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function arg(string $key, $default = null)
    {
        return array_key_exists($key, $this->args) ? $this->args[$key] : $default;
    }

    /**
     * @return array|null
     */
    public function args(): ?array
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
            $string .= $this->alias . ':';
        }
        $string .= $this->name;

        if (!empty($this->args)) {
            $string .= '(' . implode(', ', array_map(function ($key, $value) {
                    return $key . ':' . json_encode($value);
                }, array_keys($this->args), array_values($this->args))) . ')';
        }

        if (!empty($this->fields)) {
            $last = null;
            $string .= " {\n";

            foreach ($this->fields as $field) {
                if ($last !== null && $field->on() !== $last) {
                    $string .= $padding . '  }' . PHP_EOL;
                    $last = null;
                }

                if ($last === null && $field->on() !== null) {
                    $last = $field->on();
                    $string .= $padding . '  ... on ' . $field->on() . ' {' . PHP_EOL;
                    $string .= $field->toString($level + 2) . PHP_EOL;
                } elseif ($last !== null && $field->on() === $last) {
                    $string .= $field->toString($level + 2) . PHP_EOL;
                } elseif ($last === null && $field->on() === $last) {
                    $string .= $field->toString($level + 1) . PHP_EOL;
                }
            }

            if ($last !== null) {
                $string .= $padding . '  }' . PHP_EOL;
            }

            $string .= $padding . "}";
        }

        return $string;
    }
}
