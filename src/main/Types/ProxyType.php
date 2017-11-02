<?php

namespace Chemisus\GraphQL\Types;

use Chemisus\GraphQL\Node;
use Chemisus\GraphQL\Schema;
use Chemisus\GraphQL\Type;

class ProxyType implements Type
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var string
     */
    private $name;

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

    public function kind()
    {
        return $this->dereference()->kind();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description()
    {
        return $this->dereference()->description();
    }

    public function field(string $name)
    {
        return $this->dereference()->field($name);
    }

    public function fields()
    {
        return $this->dereference()->fields();
    }

    public function enumValues()
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

    public function types(Node $node, $values)
    {
        return $this->dereference()->types($node, $values);
    }

    public function interfaces()
    {
        return $this->dereference()->interfaces();
    }

    public function possibleTypes()
    {
        return $this->dereference()->possibleTypes();
    }

    public function inputFields()
    {
        return $this->dereference()->inputFields();
    }

    public function ofType()
    {
        return $this->dereference()->ofType();
    }

}