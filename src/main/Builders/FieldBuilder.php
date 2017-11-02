<?php

namespace Chemisus\GraphQL\Builders;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\Schema;
use Chemisus\GraphQL\Type;

class FieldBuilder
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
     * @var ArgumentBuilder[]
     */
    private $arguments;

    /**
     * @var DirectiveBuilder[]
     */
    private $directives;

    /**
     * @var TypeBuilder
     */
    private $type;

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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
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
     * @return ArgumentBuilder[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param ArgumentBuilder[] $arguments
     * @return self
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return TypeBuilder
     */
    public function getType(): TypeBuilder
    {
        return $this->type;
    }

    /**
     * @param TypeBuilder $type
     * @return self
     */
    public function setType(TypeBuilder $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function build(Schema $schema, Type $ownerType): Field
    {
        $value = new Field($ownerType, $this->name, $this->type->build($schema));
        return $value;
    }
}