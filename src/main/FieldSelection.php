<?php

namespace Chemisus\GraphQL;

class FieldSelection implements Selection
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
     * @var Selection[]
     */
    private $selections;

    /**
     * @param string $name
     * @param Selection[] $fields
     * @param null|string $alias
     * @param null|string $on
     * @param array|null $args
     */
    public function __construct(string $name, array $fields, ?string $alias = null, ?string $on = null, ?array $args = null)
    {
        $this->name = $name;
        $this->selections = $fields;
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
     * @return Selection[]
     */
    public function selections()
    {
        return $this->selections;
    }

    /**
     * @param string $on
     * @return Selection[]
     */
    public function fields(?string $on = null)
    {
        return array_merge([], ...array_map(function (Selection $selection) use ($on) {
            return $selection->flatten($on);
        }, $this->selections));
    }

    public function flatten(?string $on = null)
    {
        return $this->on === null || $this->on === $on ? [$this] : [];
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

        if (!empty($this->selections)) {
            $last = null;
            $string .= " {\n";

            foreach ($this->selections as $field) {
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
