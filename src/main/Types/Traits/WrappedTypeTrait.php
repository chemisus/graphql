<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Field;

trait WrappedTypeTrait
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $namePattern;

    public function name(): string
    {
        if ($this->name === null) {
            $this->name = sprintf($this->namePattern, $this->type->name());
        }

        return $this->name;
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name): Field
    {
        return $this->type->field($name);
    }

    public function fields(): ?array
    {
        return $this->type->fields();
    }

    public function type(Node $node, $value): Type
    {
        return $this->type->type($node, $value);
    }

    public function possibleTypes(): array
    {
        return $this->type->possibleTypes();
    }

    public function ofType(): ?Type
    {
        return $this->type;
    }
}
