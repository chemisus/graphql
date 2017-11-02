<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Schema;
use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\Traits\NameTrait;

class ProxyType implements Type
{
    use NameTrait;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var Type
     */
    private $type;

    /**
     * @param Schema $schema
     * @param string $name
     */
    public function __construct(Schema $schema, string $name)
    {
        $this->schema = $schema;
        $this->name = $name;
    }

    public function dereference()
    {
        if ($this->type === null) {
            $this->type = $this->schema->getType($this->name);
        }

        return $this->type;
    }

    public function kind(): string
    {
        return $this->dereference()->kind();
    }

    public function description(): ?string
    {
        return $this->dereference()->description();
    }

    public function field(string $name): Field
    {
        return $this->dereference()->field($name);
    }

    public function fields(): ?array
    {
        return $this->dereference()->fields();
    }

    public function enumValues(): ?array
    {
        return $this->dereference()->enumValues();
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->dereference()->resolve($node, $parent, $value);
    }

    public function type(Node $node, $value): Type
    {
        return $this->dereference()->type($node, $value);
    }

    public function interfaces(): ?array
    {
        return $this->dereference()->interfaces();
    }

    public function possibleTypes(): array
    {
        return $this->dereference()->possibleTypes();
    }

    public function inputFields(): ?array
    {
        return $this->dereference()->inputFields();
    }

    public function ofType(): ?Type
    {
        return $this->dereference()->ofType();
    }
}
