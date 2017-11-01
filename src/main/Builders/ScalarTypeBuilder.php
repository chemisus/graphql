<?php

namespace Chemisus\GraphQL\Builders;

use Chemisus\GraphQL\ScalarType;
use Chemisus\GraphQL\Schema;
use Chemisus\GraphQL\Type;

class ScalarTypeBuilder implements TypeBuilder
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

    public function build(Schema $schema): Type
    {
        $value = new ScalarType($this->name);
        $schema->putType($value);
        return $value;
    }
}