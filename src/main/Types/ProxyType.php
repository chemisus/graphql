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

    public function type()
    {
        if ($this->type === null) {
            $this->type = $this->schema->getType($this->name);
        }

        return $this->type;
    }

    public function kind()
    {
        return $this->type()->kind();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description()
    {
        return $this->type()->description();
    }

    public function field(string $name)
    {
        return $this->type()->field($name);
    }

    public function fields()
    {
        return $this->type()->fields();
    }

    public function enumValues()
    {
        return $this->type()->enumValues();
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->type()->resolve($node, $parent, $value);
    }

    public function typeOf(Node $node, $value): Type
    {
        return $this->type()->typeOf($node, $value);
    }

    public function types(Node $node, $values)
    {
        return $this->type()->types($node, $values);
    }

    public function interfaces()
    {
        return $this->type()->interfaces();
    }

    public function possibleTypes()
    {
        return $this->type()->possibleTypes();
    }

    public function inputFields()
    {
        return $this->type()->inputFields();
    }

    public function ofType()
    {
        return $this->type()->ofType();
    }

}