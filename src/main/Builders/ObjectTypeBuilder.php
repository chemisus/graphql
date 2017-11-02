<?php

namespace Chemisus\GraphQL\Builders;

use Chemisus\GraphQL\Type;
use Chemisus\GraphQL\Types\ObjectType;
use Chemisus\GraphQL\Types\Schema;

class ObjectTypeBuilder implements TypeBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var DirectiveBuilder[]
     */
    private $directives;

    /**
     * @var FieldBuilder[]
     */
    private $fields;

    /**
     * @var InterfaceBuilder[]
     */
    private $interfaces;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     * @return ScalarTypeBuilder
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return DirectiveBuilder[]
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /**
     * @param DirectiveBuilder[] $directives
     * @return self
     */
    public function setDirectives(array $directives): self
    {
        $this->directives = $directives;
        return $this;
    }

    /**
     * @return FieldBuilder[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param FieldBuilder[] $fields
     * @return self
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return InterfaceBuilder[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @param InterfaceBuilder[] $interfaces
     * @return self
     */
    public function setInterfaces(array $interfaces): self
    {
        $this->interfaces = $interfaces;
        return $this;
    }

    public function build(Schema $schema): Type
    {
        $value = new ObjectType($this->name);
        $schema->putType($value);
        foreach ($this->fields as $field) {
            $value->addField($field->build($schema, $value));
        }
        return $value;
    }
}