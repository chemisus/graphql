<?php

namespace Chemisus\GraphQL\Demo;

use Chemisus\GraphQL\Http;
use Exception;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\OperationTypeDefinitionNode;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\Variable;
use GraphQL\Language\Parser;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\all;

error_reporting(E_ALL);

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

function exception_error_handler($errno, $errstr, $errfile, $errline)
{
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler("exception_error_handler");

interface Type
{
    /**
     * @return string
     */
    public function getKind(): string;

    /**
     * @return null|string
     */
    public function getName(): ?string;

    /**
     * @return null|string
     */
    public function getDescription(): ?string;

    public function getField(string $name): Field;

    public function getFields();

    public function setCoercer(Coercer $coercer);

//    public function setTyper(Typer $typer);

    public function coerce(Node $node, $value);

    public function resolve(Node $node, $parent, $value);
}

trait CoercerTrait
{
    /**
     * @var Coercer
     */
    private $coercer;

    public function coerce(Node $node, $value)
    {
        return $this->coercer !== null ? $this->coercer->coerce($node, $value) : $value;
    }

    public function setCoercer(Coercer $coercer)
    {
        $this->coercer = $coercer;
    }
}

trait TyperTrait
{
    /**
     * @var Typer
     */
    private $typer;

    public function setTyper(Typer $typer)
    {
        $this->typer = $typer;
    }

    public function type(Node $node, $value): Type
    {
        return $this->typer->type($node, $value);
    }
}

trait FetcherTrait
{
    /**
     * @var Fetcher
     */
    private $fetcher;

    public function setFetcher(Fetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }
}

trait ResolverTrait
{
    /**
     * @var Resolver
     */
    private $resolver;

    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }
}

trait NullFieldTrait
{
    public function getField(string $name): Field
    {
        return null;
    }

    public function getFields()
    {
        return [];
    }
}

trait NameTrait
{
    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }
}

trait ValueTrait
{
    /**
     * @var
     */
    private $value;

    /**
     * @return
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }
}

trait OperationTrait
{
    /**
     * @var string
     */
    private $operation;

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     * @return self
     */
    public function setOperation(string $operation): self
    {
        $this->operation = $operation;
        return $this;
    }
}

trait SelectionSetTrait
{
    /**
     * @var SelectionSet|null
     */
    private $selectionSet;

    /**
     * @return SelectionSet|null
     */
    public function getSelectionSet(): ?SelectionSet
    {
        return $this->selectionSet;
    }

    /**
     * @param SelectionSet|null $selectionSet
     * @return self
     */
    public function setSelectionSet(?SelectionSet $selectionSet): self
    {
        $this->selectionSet = $selectionSet;
        return $this;
    }
}

trait SelectionsTrait
{
    /**
     * @var Selection[]
     */
    private $selections;

    /**
     * @return Selection[]
     */
    public function getSelections()
    {
        return $this->selections;
    }

    /**
     * @param Selection[] $selections
     * @return self
     */
    public function setSelections($selections): self
    {
        $this->selections = $selections;
        return $this;
    }
}

trait TypeTrait
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @param Type $type
     * @return self
     */
    public function setType(Type $type): self
    {
        $this->type = $type;
        return $this;
    }
}

trait TypeConditionTrait
{
    /**
     * @var Type
     */
    private $typeCondition;

    /**
     * @return Type
     */
    public function getTypeCondition(): Type
    {
        return $this->typeCondition;
    }

    /**
     * @param Type $typeCondition
     * @return self
     */
    public function setTypeCondition(Type $typeCondition): self
    {
        $this->typeCondition = $typeCondition;
        return $this;
    }
}

trait QueryTrait
{
    /**
     * @var ObjectType
     */
    private $query;

    /**
     * @return ObjectType
     */
    public function getQuery(): ObjectType
    {
        return $this->query;
    }

    /**
     * @param ObjectType $query
     * @return self
     */
    public function setQuery(ObjectType $query): self
    {
        $this->query = $query;
        return $this;
    }
}

trait MutationTrait
{
    /**
     * @var Type
     */
    private $mutation;

    /**
     * @return string
     */
    public function getMutation(): string
    {
        return $this->mutation;
    }

    /**
     * @param Type $mutation
     * @return self
     */
    public function setMutation(Type $mutation): self
    {
        $this->mutation = $mutation;
        return $this;
    }
}

trait DescriptionTrait
{
    /**
     * @var string
     */
    private $description;

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}

trait DirectivesTrait
{
    /**
     * @var string
     */
    private $directives;

    /**
     * @return string
     */
    public function getDirectives(): string
    {
        return $this->directives;
    }

    /**
     * @param array|null $directives
     * @return self
     */
    public function setDirectives(?array $directives): self
    {
        $this->directives = $directives;
        return $this;
    }
}

trait FieldsTrait
{
    /**
     * @var Field[]
     */
    private $fields = [];

    public function getField(string $name): Field
    {
        return $this->fields[$name];
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array|null $fields
     * @return self
     */
    public function setFields(?array $fields): self
    {
        $this->fields = [];
        foreach ($fields as $field) {
            $this->fields[$field->getName()] = $field;
        }
        return $this;
    }
}

trait InterfacesTrait
{
    /**
     * @var string
     */
    private $interfaces;

    /**
     * @return string
     */
    public function getInterfaces(): string
    {
        return $this->interfaces;
    }

    /**
     * @param array|null $interfaces
     * @return self
     */
    public function setInterfaces(?array $interfaces): self
    {
        $this->interfaces = $interfaces;
        return $this;
    }
}

trait TypesTrait
{
    /**
     * @var string
     */
    private $types;

    /**
     * @return string
     */
    public function getTypes(): string
    {
        return $this->types;
    }

    /**
     * @param array|null $types
     * @return self
     */
    public function setTypes(?array $types): self
    {
        $this->types = $types;
        return $this;
    }
}

trait ArgumentsTrait
{
    /**
     * @var array
     */
    private $arguments;

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array|null $arguments
     * @return self
     */
    public function setArguments(?array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }
}

trait ValuesTrait
{
    /**
     * @var string
     */
    private $values;

    /**
     * @return string
     */
    public function getValues(): string
    {
        return $this->values;
    }

    /**
     * @param array|null $values
     * @return self
     */
    public function setValues(?array $values): self
    {
        $this->values = $values;
        return $this;
    }
}

trait DefaultValueTrait
{
    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     * @return self
     */
    public function setDefaultValue($defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }
}

trait DeprecatedTrait
{
    /**
     * @var string
     */
    private $isDeprecated;

    /**
     * @var string
     */
    private $deprecationReason;

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @param string|null $isDeprecated
     * @return self
     */
    public function setIsDeprecated(?string $isDeprecated): self
    {
        $this->isDeprecated = $isDeprecated;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason;
    }

    /**
     * @param string|null $deprecationReason
     * @return self
     */
    public function setDeprecationReason(?string $deprecationReason): self
    {
        $this->deprecationReason = $deprecationReason;
        return $this;
    }
}

class ScalarType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use NullFieldTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return 'SCALAR';
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->coerce($node, $value);
    }
}

class ObjectType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use FieldsTrait;
    use InterfacesTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return 'OBJECT';
    }

    public function resolve(Node $node, $parent, $value)
    {
        if ($value === null) {
            return null;
        }

        $coerced = $this->coerce($node, $value) ?? (object)[];

        $object = (object) [];

        foreach ($node->getChildren() as $child) {
            $name = $child->getField()->getName();
            $field = isset($coerced->{$name}) ? $coerced->{$name} : (isset($value->{$name}) ? $value->{$name} : null);
            $object->{$child->getSelection()->getAlias()} = $child->resolve($value, $field);
        }

        return $object;
    }
}

class InterfaceType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use FieldsTrait;
    use TypesTrait;
    use CoercerTrait;
    use TyperTrait;

    public function getKind(): string
    {
        return 'INTERFACE';
    }

    public function getField(string $name): Field
    {
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->type($node, $value)->resolve($node, $parent, $value);
    }
}

class EnumType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use ValuesTrait;
    use NullFieldTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return 'ENUM';
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->coerce($node, $value);
    }
}

class UnionType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use TypesTrait;
    use NullFieldTrait;
    use CoercerTrait;
    use TyperTrait;

    public function getKind(): string
    {
        return 'UNION';
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $this->type($node, $value)->resolve($node, $parent, $value);
    }
}

class InputObjectType implements Type
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use FieldsTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return 'INPUT_OBJECT';
    }

    public function getField(string $name): Field
    {
    }

    public function resolve(Node $node, $parent, $value)
    {
    }
}

class ListType implements Type
{
    use TypeTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return 'LIST';
    }

    public function getName(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getField(string $name): Field
    {
        return $this->getType()->getField($name);
    }

    public function getFields()
    {
        return $this->getType()->getFields();
    }

    public function resolve(Node $node, $parent, $value)
    {
        return $value === null ? null : array_map(function ($value) use ($node, $parent) {
            return $this->getType()->resolve($node, $parent, $value);
        }, $value);
    }
}

class NonNullType implements Type
{
    use TypeTrait;
    use CoercerTrait;

    public function getKind(): string
    {
        return 'NON_NULL';
    }

    public function getName(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getField(string $name): Field
    {
        return $this->getType()->getField($name);
    }

    public function getFields()
    {
        return $this->getType()->getFields();
    }

    public function resolve(Node $node, $parent, $value)
    {
        $value = $this->getType()->resolve($node, $parent, $value);

        if ($value === null) {
            throw new Exception("%s can not be null", $node->getPath());
        }

        return $value;
    }
}

class Field implements Fetcher, Resolver
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use TypeTrait;
    use ArgumentsTrait;
    use FetcherTrait;
    use ResolverTrait;

    public function fetch(Node $node, $parents)
    {
        return $this->fetcher ? $this->fetcher->fetch($node, $parents) : [];
    }

    public function resolve(Node $node, $parent, $value)
    {
        $value = $this->resolver ? $this->resolver->resolve($node, $parent, $value) : $value;
        return $this->getType()->resolve($node, $parent, $value);
    }
}

class InputValue
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use TypeTrait;
    use DefaultValueTrait;
}

class EnumValue
{
    use NameTrait;
    use DescriptionTrait;
    use DirectivesTrait;
    use TypeTrait;
    use DefaultValueTrait;
}

class Schema
{
    use TypesTrait;

    private $operationTypes = [];

    public function getOperation(string $operation): ObjectType
    {
        return $this->operationTypes[$operation];
    }

    public function getQuery(): ObjectType
    {
        return $this->operationTypes['query'];
    }

    public function getMutation(): ?ObjectType
    {
        return array_key_exists('mutation', $this->operationTypes) ? $this->operationTypes['mutation'] : null;
    }

    /**
     * @param OperationType[] $operationTypes
     */
    public function setOperationTypes($operationTypes)
    {
        foreach ($operationTypes as $operationType) {
            $this->operationTypes[$operationType->getOperation()] = $operationType->getType();
        }
    }
}

class OperationType
{
    use OperationTrait;
    use TypeTrait;
}

class Operation
{
    use NameTrait;
    use DirectivesTrait;
    use OperationTrait;
    use SelectionSetTrait;
}

interface Selection
{
    /**
     * @param Type|null $on
     * @return FieldSelection[]
     */
    public function flatten(?Type $on = null);
}

class Fragment implements Selection
{
    use NameTrait;
    use DirectivesTrait;
    use SelectionSetTrait;
    use TypeConditionTrait;

    public function flatten(?Type $on = null)
    {
        return $this->getSelectionSet()->flatten($on);
    }
}

class SelectionSet implements Selection
{
    use SelectionsTrait;

    public function flatten(?Type $on = null)
    {
        return array_merge([], ...array_map(function (Selection $selection) use ($on) {
            return $selection->flatten($on);
        }, $this->getSelections()));
    }
}

class FieldSelection implements Selection
{
    use NameTrait;
    use DirectivesTrait;
    use SelectionSetTrait;
    use TypeTrait;
    use ArgumentsTrait;

    /**
     * @var string|null
     */
    private $alias;

    public function getAlias(): string
    {
        return $this->alias ?? $this->getName();
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @param Type|null $on
     * @return FieldSelection[]
     */
    public function flatten(?Type $on = null)
    {
        return [$this];
    }

    /**
     * @param Type|null $on
     * @return FieldSelection[]
     */
    public function fields(?Type $on = null)
    {
        return $this->getSelectionSet() ? $this->getSelectionSet()->flatten($on) : [];
    }
}

class Argument
{
    use NameTrait;
    use ValueTrait;
}

class FragmentSpread implements Selection
{
    use NameTrait;
    use DirectivesTrait;

    /**
     * @var Document
     */
    private $document;

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @param Document $document
     * @return self
     */
    public function setDocument(Document $document): self
    {
        $this->document = $document;
        return $this;
    }

    public function getFragment(): Fragment
    {
        return $this->getDocument()->fragments[$this->getName()];
    }

    public function flatten(?Type $on = null)
    {
        return $this->getFragment()->flatten($on);
    }
}

class InlineFragment implements Selection
{
    use NameTrait;
    use DirectivesTrait;
    use SelectionSetTrait;
    use TypeConditionTrait;

    public function flatten(?Type $on = null)
    {
        return $this->getSelectionSet()->flatten($on);
    }
}

class Document
{
    /**
     * @var Schema
     */
    public $schema;

    /**
     * @var Type[]
     */
    public $types = [];

    /**
     * @var Operation[]
     */
    public $operations = [];

    /**
     * @var Fragment[]
     */
    public $fragments = [];

    public function getOperation(?string $name = 'Query'): Operation
    {
        return $this->operations[$name];
    }

    public function coercer(string $type, Coercer $coercer)
    {
        $this->types[$type]->setCoercer($coercer);
    }

    public function typer(string $type, Typer $typer)
    {
        $this->types[$type]->setTyper($typer);
    }

    public function fetcher(string $type, string $field, Fetcher $fetcher)
    {
        $this->types[$type]->getField($field)->setFetcher($fetcher);
    }

    public function resolver(string $type, string $field, Resolver $resolver)
    {
        $this->types[$type]->getField($field)->setResolver($resolver);
    }
}

interface Coercer
{
    public function coerce(Node $node, $value);
}

interface Fetcher
{
    public function fetch(Node $node, $parents);
}

interface Resolver
{
    public function resolve(Node $node, $parent, $value);
}

interface Typer
{
    public function type(Node $node, $value): Type;
}

interface Factory
{
    public function make(DocumentBuilder $builder, Document $document, $node);
}

interface Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node);
}

class ScalarTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        return $document->types[$builder->buildNode($node->name)] = new ScalarType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ScalarType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        return $type;
    }
}

class InterfaceTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InterfaceTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new InterfaceType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InterfaceTypeDefinitionNode $node
         * @var InterfaceType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setFields($builder->buildNodes($node->fields));
        return $type;
    }
}

class ObjectTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ObjectTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new ObjectType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ObjectTypeDefinitionNode $node
         * @var ObjectType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setInterfaces($builder->buildNodes($node->interfaces));
        $type->setFields($builder->buildNodes($node->fields));
        return $type;
    }
}

class EnumTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var EnumTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new EnumType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var EnumTypeDefinitionNode $node
         * @var EnumType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setValues($builder->buildNodes($node->values));
        return $type;
    }
}

class InputObjectTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InputObjectTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new InputObjectType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InputObjectTypeDefinitionNode $node
         * @var InputObjectType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setFields($builder->buildNodes($node->fields));
        return $type;
    }
}

class UnionTypeBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var UnionTypeDefinitionNode $node
         */
        $document->types[$builder->buildNode($node->name)] = new UnionType();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var UnionTypeDefinitionNode $node
         * @var UnionType $type
         */
        $type = $document->types[$builder->buildNode($node->name)];
        $type->setName($builder->buildNode($node->name));
        $type->setDescription($node->description);
        $type->setDirectives($builder->buildNodes($node->directives));
        $type->setTypes($builder->buildNodes($node->types));
        return $type;
    }
}

class OperationBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var OperationDefinitionNode $node
         */
        $document->operations[$builder->buildNode($node->name) ?? 'Query'] = new Operation();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var OperationDefinitionNode $node
         * @var Operation $operation
         */
        $name = $builder->buildNode($node->name) ?? 'Query';
        $operation = $document->operations[$name];
        $operation->setName($name);
        $operation->setDirectives($node->directives);
        $operation->setOperation($node->operation);
        $operation->setSelectionSet($builder->buildNode($node->selectionSet));
        $node->variableDefinitions;
        return $operation;
    }
}

class FragmentBuilder implements Factory, Builder
{
    public function make(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FragmentDefinitionNode $node
         */
        $document->fragments[$builder->buildNode($node->name)] = new Fragment();
    }

    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FragmentDefinitionNode $node
         * @var Fragment $fragment
         */
        $fragment = $document->fragments[$builder->buildNode($node->name)];
        $fragment->setName($builder->buildNode($node->name));
        $fragment->setDirectives($node->directives);
        $fragment->setSelectionSet($builder->buildNode($node->selectionSet));
        $fragment->setTypeCondition($builder->buildNode($node->typeCondition));
        return $fragment;
    }
}

class ValueBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        return $node->value;
    }
}

class NullBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        return null;
    }
}

class FieldSelectionBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FieldNode $node
         */
        $field = new FieldSelection();
        $field->setName($builder->buildNode($node->name));
        $field->setDirectives($node->directives);
        $field->setSelectionSet($builder->buildNode($node->selectionSet));
        $field->setArguments(array_merge([], ...$builder->buildNodes($node->arguments)));
        $field->setAlias($builder->buildNode($node->alias));
        return $field;
    }
}

class SchemaBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var SchemaDefinitionNode $node
         */
        $document->schema = $built = new Schema();
        $built->setOperationTypes($builder->buildNodes($node->operationTypes));
        return $built;
    }
}

class VariableBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var Variable $node
         */
        return $builder->buildNode($node->name);
    }
}

class LstBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ListValueNode $node
         */
        return $builder->buildNodes($node->values);
    }
}

class EnumValueBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var EnumValueDefinitionNode $node
         */
        $built = new EnumValue();
        $built->setName($builder->buildNode($node->name));
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        return $built;
    }
}

class InputValueBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InputValueDefinitionNode $node
         */
        $built = new InputValue();
        $built->setName($builder->buildNode($node->name));
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setType($builder->buildNode($node->type));
        $built->setDefaultValue($builder->buildNode($node->defaultValue));
        return $built;
    }
}

class ListBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ListTypeNode $node
         */
        $built = new ListType();
        $built->setType($builder->buildNode($node->type));
        return $built;
    }
}

class NonNullBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var NonNullTypeNode $node
         */
        $built = new NonNullType();
        $built->setType($builder->buildNode($node->type));
        return $built;
    }
}

class NamedTypeBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        return $document->types[$builder->buildNode($node->name)];
    }
}

class FieldBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FieldDefinitionNode $node
         */
        $built = new Field();
        $built->setName($builder->buildNode($node->name));
        $built->setDescription($node->description);
        $built->setDirectives($builder->buildNodes($node->directives));
        $built->setArguments($builder->buildNodes($node->arguments));
        $built->setType($builder->buildNode($node->type));
        return $built;
    }
}

class SelectionSetBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var SelectionSetNode $node
         */
        $built = new SelectionSet();
        $built->setSelections($builder->buildNodes($node->selections));
        return $built;
    }
}

class ArgumentBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var ArgumentNode $node
         */
        return [$builder->buildNode($node->name) => $builder->buildNode($node->value)];
    }
}

class FragmentSpreadBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var FragmentSpreadNode $node
         */
        $built = new FragmentSpread();
        $built->setName($builder->buildNode($node->name));
        $built->setDirectives($node->directives);
        $built->setDocument($document);
        return $built;
    }
}

class InlineFragmentBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var InlineFragmentNode $node
         */
        $built = new InlineFragment();
        $built->setTypeCondition($builder->buildNode($node->typeCondition));
        $built->setSelectionSet($builder->buildNode($node->selectionSet));
        $built->setDirectives($node->directives);
        return $built;
    }
}

class OperationTypeBuilder implements Builder
{
    public function build(DocumentBuilder $builder, Document $document, $node)
    {
        /**
         * @var OperationTypeDefinitionNode $node
         */
        $built = new OperationType();
        $built->setOperation($node->operation);
        $built->setType($builder->buildNode($node->type));
        return $built;
    }
}

class DocumentBuilder
{
    private $factories;

    private $builders;

    private $source = '';

    private $document;

    public function __construct()
    {
        $this->document = new Document();

        $valueBuilder = new ValueBuilder();

        $builders = [
            NodeKind::SCALAR_TYPE_DEFINITION => new ScalarTypeBuilder(),
            NodeKind::INTERFACE_TYPE_DEFINITION => new InterfaceTypeBuilder(),
            NodeKind::OBJECT_TYPE_DEFINITION => new ObjectTypeBuilder(),
            NodeKind::ENUM_TYPE_DEFINITION => new EnumTypeBuilder(),
            NodeKind::INPUT_OBJECT_TYPE_DEFINITION => new InputObjectTypeBuilder(),
            NodeKind::UNION_TYPE_DEFINITION => new UnionTypeBuilder(),

            NodeKind::OPERATION_DEFINITION => new OperationBuilder(),
            NodeKind::FRAGMENT_DEFINITION => new FragmentBuilder(),
        ];

        $this->factories = [
            NodeKind::SCALAR_TYPE_DEFINITION => $builders[NodeKind::SCALAR_TYPE_DEFINITION],
            NodeKind::INTERFACE_TYPE_DEFINITION => $builders[NodeKind::INTERFACE_TYPE_DEFINITION],
            NodeKind::OBJECT_TYPE_DEFINITION => $builders[NodeKind::OBJECT_TYPE_DEFINITION],
            NodeKind::ENUM_TYPE_DEFINITION => $builders[NodeKind::ENUM_TYPE_DEFINITION],
            NodeKind::INPUT_OBJECT_TYPE_DEFINITION => $builders[NodeKind::INPUT_OBJECT_TYPE_DEFINITION],
            NodeKind::UNION_TYPE_DEFINITION => $builders[NodeKind::UNION_TYPE_DEFINITION],

            //=========================================================================================================

            NodeKind::OPERATION_DEFINITION => $builders[NodeKind::OPERATION_DEFINITION],
            NodeKind::FRAGMENT_DEFINITION => $builders[NodeKind::FRAGMENT_DEFINITION],
        ];

        $this->builders = [
            NodeKind::SCHEMA_DEFINITION => new SchemaBuilder(),
            NodeKind::SCALAR_TYPE_DEFINITION => $builders[NodeKind::SCALAR_TYPE_DEFINITION],
            NodeKind::INTERFACE_TYPE_DEFINITION => $builders[NodeKind::INTERFACE_TYPE_DEFINITION],
            NodeKind::OBJECT_TYPE_DEFINITION => $builders[NodeKind::OBJECT_TYPE_DEFINITION],
            NodeKind::ENUM_TYPE_DEFINITION => $builders[NodeKind::ENUM_TYPE_DEFINITION],
            NodeKind::INPUT_OBJECT_TYPE_DEFINITION => $builders[NodeKind::INPUT_OBJECT_TYPE_DEFINITION],
            NodeKind::UNION_TYPE_DEFINITION => $builders[NodeKind::UNION_TYPE_DEFINITION],

            //=========================================================================================================

            NodeKind::VARIABLE => new VariableBuilder(),
            NodeKind::ENUM_VALUE_DEFINITION => new EnumValueBuilder(),
            NodeKind::INPUT_VALUE_DEFINITION => new InputValueBuilder(),
            NodeKind::LIST_TYPE => new ListBuilder(),
            NodeKind::NON_NULL_TYPE => new NonNullBuilder(),
            NodeKind::NAMED_TYPE => new NamedTypeBuilder(),
            NodeKind::FIELD_DEFINITION => new FieldBuilder(),

            //=========================================================================================================

            NodeKind::OPERATION_TYPE_DEFINITION => new OperationTypeBuilder(),
            NodeKind::OPERATION_DEFINITION => $builders[NodeKind::OPERATION_DEFINITION],
            NodeKind::FRAGMENT_DEFINITION => $builders[NodeKind::FRAGMENT_DEFINITION],

            //=========================================================================================================

            NodeKind::NAME => $valueBuilder,
            NodeKind::FLOAT => $valueBuilder,
            NodeKind::STRING => $valueBuilder,
            NodeKind::BOOLEAN => $valueBuilder,
            NodeKind::INT => $valueBuilder,
            NodeKind::ENUM => $valueBuilder,
            NodeKind::NULL => new NullBuilder(),
            NodeKind::LST => new LstBuilder(),

            //=========================================================================================================

            NodeKind::FIELD => new FieldSelectionBuilder(),
            NodeKind::SELECTION_SET => new SelectionSetBuilder(),
            NodeKind::ARGUMENT => new ArgumentBuilder(),
            NodeKind::FRAGMENT_SPREAD => new FragmentSpreadBuilder(),
            NodeKind::INLINE_FRAGMENT => new InlineFragmentBuilder(),
        ];
    }

    public function make($node)
    {
        $maker = $this->factories[$node->kind];

        if ($maker instanceof Factory) {
            return $maker->make($this, $this->document, $node);
        }

        return call_user_func($maker, $node);
    }

    public function buildNode($node)
    {
        if ($node === null) {
            return null;
        }

        $builder = $this->builders[$node->kind];

        if ($builder instanceof Builder) {
            return $builder->build($this, $this->document, $node);
        }

        return call_user_func($builder, $node);
    }

    public function buildNodes($nodes)
    {
        return array_map([$this, 'buildNode'], $nodes);
    }

    public function load($source)
    {
        $this->source .= PHP_EOL . $source;
        return $this;
    }

    public function build()
    {
        $this->buildSchema();
        $this->buildOperations();
        return $this->document;
    }

    public function buildOperations()
    {
        $document = json_decode(json_encode(Parser::parse($this->source)->toArray(true)));

        $kinds = [
            NodeKind::VARIABLE_DEFINITION,
            NodeKind::FRAGMENT_DEFINITION,
            NodeKind::OPERATION_DEFINITION,
        ];

        $nodes = $this->kinds($document, ...$kinds);

        foreach ($nodes as $node) {
            printf("\nmaking %s", $this->buildNode($node->name) ?? 'Query');
            $this->make($node);
        }

        foreach ($nodes as $node) {
            printf("\nbuilding %s", $this->buildNode($node->name) ?? 'Query');
            $this->buildNode($node);
        }
    }

    public function buildSchema()
    {
        $document = json_decode(json_encode(Parser::parse($this->source)->toArray(true)));

        $kinds = [
            NodeKind::SCALAR_TYPE_DEFINITION,
            NodeKind::INTERFACE_TYPE_DEFINITION,
            NodeKind::OBJECT_TYPE_DEFINITION,
            NodeKind::ENUM_TYPE_DEFINITION,
            NodeKind::INPUT_OBJECT_TYPE_DEFINITION,
            NodeKind::UNION_TYPE_DEFINITION,
        ];

        $nodes = $this->kinds($document, ...$kinds);

        foreach ($nodes as $node) {
            printf("\nmaking %s", $this->buildNode($node->name));
            $this->make($node);
        }

        foreach ($this->kinds($document, ...$kinds) as $node) {
            printf("\nbuilding %s", $this->buildNode($node->name));
            $this->buildNode($node);
        }

        $kinds = [
            NodeKind::SCHEMA_DEFINITION,
        ];

        $nodes = $this->kinds($document, ...$kinds);

        foreach ($nodes as $node) {
            printf("\nlinking schema");
            $this->buildNode($node);
        }
    }

    private function kinds($document, ...$kinds)
    {
        return array_merge([], ...array_map(function ($kind) use ($document) {
            return array_filter($document->definitions, function ($definition) use ($kind) {
                return $definition->kind === $kind;
            });
        }, $kinds));
    }
}

class CallbackCoercer implements Coercer
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function coerce(Node $node, $value)
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}

class CallbackTyper implements Typer
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function type(Node $node, $value): Type
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}

class CallbackFetcher implements Fetcher
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function fetch(Node $node, $parents)
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}

class CallbackResolver implements Resolver
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function resolve(Node $node, $parent, $value)
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}

class Node
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var Field
     */
    private $field;

    /**
     * @var FieldSelection
     */
    private $selection;

    /**
     * @var Node
     */
    private $parent;

    /**
     * @var Node[]
     */
    private $children = [];

    /**
     * @param Schema $schema
     * @param Field $field
     * @param FieldSelection $selection
     * @param Node $parent
     */
    public function __construct(Schema $schema, Field $field, FieldSelection $selection, Node $parent = null)
    {
        $this->schema = $schema;
        $this->field = $field;
        $this->selection = $selection;
        $this->parent = $parent;
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * @return FieldSelection
     */
    public function getSelection(): FieldSelection
    {
        return $this->selection;
    }

    /**
     * @return Node
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }

    public function addChild(Node $child)
    {
        $this->children[] = $child;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getPath(): string
    {
        $parent = $this->getParent();
        $path = $this->getField()->getName();

        while ($parent) {
            $path = sprintf('%s.%s', $parent->getField()->getName(), $path);
            $parent = $parent->getParent();
        }

        return $path;
    }

    public function fetch($parents)
    {
        printf("\nFETCHING %s", $this->getPath());
        return $this->getField()->fetch($this, $parents);
    }

    public function resolve($parent, $value)
    {
        printf("\nRESOLVING %s", $this->getPath());
        return $this->getField()->resolve($this, $parent, $value);
    }
}

class Executor
{
    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(LoopInterface $loop = null)
    {
        $this->loop = $loop ?? \React\EventLoop\Factory::create();
        Http::init($this->loop);
    }

    public function makeRootNodes(Document $document, Operation $operation, Type $type)
    {
        return array_map(function (FieldSelection $field) use ($document, $operation, $type) {
            return $this->makeRootNode($document, $type->getField($field->getName()), $field);
        }, $operation->getSelectionSet()->flatten());
    }

    public function makeRootNode(Document $document, Field $field, FieldSelection $selection)
    {
        return new Node($document->schema, $field, $selection);
    }

    public function makeChildNode(Document $document, FieldSelection $field, Node $parent)
    {
        return new Node($document->schema, $parent->getField()->getType()->getField($field->getName()), $field, $parent);
    }

    public function execute(Document $document, string $operation = 'Query')
    {
        /**
         * @var Node[] $roots
         * @var PromiseInterface[] $fetchers
         */
        $roots = $this->makeRootNodes($document, $document->operations[$operation], $document->schema->getOperation($document->operations[$operation]->getOperation()));

        $value = [];

        $this->fetch($document, $roots)->then(function () use (&$value, $roots) {
            foreach($roots as $root) {
                $value[$root->getSelection()->getAlias()] = $root->resolve(null, null);
            }
        });

        $root = $roots[0];

        $this->loop->run();

        return (object)$value;
    }

    /**
     * @param Document $document
     * @param Node[] $roots
     * @return PromiseInterface
     */
    public function fetch(Document $document, $roots)
    {
        $queue = [];

        $fetcher = function (Node $node, $parents = []) use (&$queue, &$fetcher) {
            $promise = all($parents)->then(function ($parents) use ($node) {
                return $node->fetch($parents);
            });
            $queue[] = $promise;
            foreach ($node->getChildren() as $child) {
                $fetcher($child, $promise);
            }
        };

        foreach ($roots as $root) {
            $this->makeChildren($document, $root);

            $fetcher($root);
        }

        return all($queue);
    }

    public function makeChildren(Document $document, Node $root)
    {
        $queue = [$root];
        while (!empty($queue)) {
            $node = array_shift($queue);
            foreach ($node->getSelection()->fields() as $field) {
                $child = $this->makeChildNode($document, $field, $node);
                $node->addChild($child);
                $queue[] = $child;
            }
        }
    }
}

class DocumentWirer
{
    public function wire(Document $document)
    {
        $graph = [];

        $document->coercer('Query', new CallbackCoercer(function (Node $node, $value) {
            return (object) [];
        }));

        $document->fetcher('Query', 'human', new CallbackFetcher(function (Node $node) use (&$graph) {
            $id = $node->getSelection()->getArguments()['id'];
            printf("\nSTART %s %s", $node->getPath(), $id);
            return Http::get(sprintf('https://swapi.co/api/people/%s/', $id))
                ->then(function ($data) use ($node, $id, &$graph) {
                    printf("\nFINISH %s %s", $node->getPath(), $id);
                    return [$graph[$id] = json_decode($data)];
                });
        }));

        $document->resolver('Query', 'human', new CallbackResolver(function (Node $node) use (&$graph) {
            return $graph[$node->getSelection()->getArguments()['id']];
        }));

        $document->coercer('Human', new CallbackCoercer(function (Node $node, $value) use (&$graph) {
            return (object)[
                'appearsIn' => $value->films,
                'starships' => $value->starships,
            ];
        }));
    }
}

call_user_func(function () {
    $source = <<< SOURCE

scalar A
scalar B

type Query {
  me: User
}

type User {
  id: ID
  name: String
}

{
  me {
    name
  }
}

{
  hero {
    name
  }
}

{
  hero {
    name
    # Queries can have comments!
    friends {
      name
    }
  }
}

{
  human(id: "1000") {
    name
    height
  }
}

{
  human(id: "1000") {
    name
    height(unit: FOOT)
  }
}

{
  empireHero: hero(episode: EMPIRE) {
    name
  }
  jediHero: hero(episode: JEDI) {
    name
  }
}

{
  leftComparison: hero(episode: EMPIRE) {
    ...comparisonFields
  }
  rightComparison: hero(episode: JEDI) {
    ...comparisonFields
  }
}

fragment comparisonFields on Character {
  name
  appearsIn
  friends {
    name
  }
}

query HeroNameAndFriends(\$episode: Episode) {
  hero(episode: \$episode) {
    name
    friends {
      name
    }
  }
}

query Hero(\$episode: Episode, \$withFriends: Boolean!) {
  hero(episode: \$episode) {
    name
    friends @include(if: \$withFriends) {
      name
    }
  }
}

mutation CreateReviewForEpisode(\$ep: Episode!, \$review: ReviewInput!) {
  createReview(episode: \$ep, review: \$review) {
    stars
    commentary
  }
}

query HeroForEpisode(\$ep: Episode!) {
  hero(episode: \$ep) {
    name
    ... on Droid {
      primaryFunction
    }
    ... on Human {
      height
    }
  }
}

{
  search(text: "an") {
    __typename
    ... on Human {
      name
    }
    ... on Droid {
      name
    }
    ... on Starship {
      name
    }
  }
}

type Mutation {
    name: String
}

mutation Mutation {
    name
}

schema {
  query: Query
  mutation: Mutation
}

query {
  hero {
    name
  }
  droid(id: "2000") {
    name
  }
}

type Query {
  hero(episode: Episode): Character
  droid(id: ID!): Droid
}

{
  hero {
    name
    appearsIn
  }
}

scalar Date
scalar String
scalar ID
scalar Int
scalar Boolean
scalar Float

type Character {
  name: String!
  appearsIn: [Episode]!
}

enum LengthUnit {
    METER
}

type Starship {
  id: ID!
  name: String!
  length(unit: LengthUnit = METER): Float
}    
    
enum Episode {
  NEWHOPE
  EMPIRE
  JEDI
}

type Character {
  name: String!
  appearsIn: [Episode]!
}

query DroidById(\$id: ID!) {
  droid(id: \$id) {
    name
  }
}

interface Character {
  id: ID!
  name: String!
  friends: [Character]
  appearsIn: [Episode]!
}

type Human implements Character {
  id: ID!
  name: String!
  friends: [Character]
  appearsIn: [Episode]!
  starships: [Starship]
  totalCredits: Int
}

type Droid implements Character {
  id: ID!
  name: String!
  friends: [Character]
  appearsIn: [Episode]!
  primaryFunction: String
}

query HeroForEpisode(\$ep: Episode!) {
  hero(episode: \$ep) {
    name
    primaryFunction
  }
}

query HeroForEpisode(\$ep: Episode!) {
  hero(episode: \$ep) {
    name
    ... on Droid {
      primaryFunction
    }
  }
}

union SearchResult = Human | Droid | Starship

{
  search(text: "an") {
    ... on Human {
      name
      height
    }
    ... on Droid {
      name
      primaryFunction
    }
    ... on Starship {
      name
      length
    }
  }
}

input ReviewInput {
  stars: Int!
  commentary: String
}

mutation CreateReviewForEpisode(\$ep: Episode!, \$review: ReviewInput!) {
  createReview(episode: \$ep, review: \$review) {
    stars
    commentary
  }
}

{
  hero {
    ...NameAndAppearances
    friends {
      ...NameAndAppearances
      friends {
        ...NameAndAppearances
      }
    }
  }
}

fragment NameAndAppearances on Character {
  name
  appearsIn
}

{
  hero {
    name
    ...DroidFields
  }
}

fragment DroidFields on Droid {
  primaryFunction
}

{
  hero {
    name
    ... on Droid {
      primaryFunction
    }
  }
}

{
  __schema {
    types {
      name
    }
  }
}

{
  __type(name: "Droid") {
    name
    fields {
      name
      type {
        name
        kind
        ofType {
          name
          kind
        }
      }
    }
  }
}

type Query {
  human(id: ID!): Human
}

type Human {
  name: String
  appearsIn: [Episode]
  starships: [Starship]
}

enum Episode {
  NEWHOPE
  EMPIRE
  JEDI
}

type Starship {
  id: ID
  name: String
}

{
  human(id: 1002) {
    name
    appearsIn
    starships {
      name
    }
  }
}

{
  a:human(id: 5) {
    name
    appearsIn
    starships {
      id
      name
    }
  }
  b:human(id: 1) {
    name
    appearsIn
    starships {
      id
      name
    }
  }
}

SOURCE;

    $builder = new DocumentBuilder();
    $builder->load($source);
    $document = $builder->build();
    $wirer = new DocumentWirer();
    $wirer->wire($document);

    $executor = new Executor();
    $result = $executor->execute($document);

    echo PHP_EOL;

    echo \GuzzleHttp\json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


//    $builder = new GraphQLSchemaBuilder();
//
//    $schema = $builder->readSchema(json_decode(json_encode(Parser::parse($source)->toArray(true))));
//
//    echo $schema->buildSchema();
});
