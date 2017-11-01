<?php

namespace GraphQL;

use GraphQL\Language\Parser;

error_reporting(E_ALL);

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class Builder
{
    private $builders;

    public function __construct()
    {
        $this->builders = $builders = [
            'Document' => function ($definition, $parent) {
                $definitions = $this->builds($definition->definitions, $definition);
            },

            'StringValue' => function ($definition, $parent) {
                return $definition->value;
            },
            'BooleanValue' => function ($definition, $parent) {
                return $definition->value;
            },
            'IntValue' => function ($definition, $parent) {
                return $definition->value;
            },
            'FloatValue' => function ($definition, $parent) {
                return $definition->value;
            },
            'NullValue' => function ($definition, $parent) {
                return null;
            },


            'SchemaDefinition' => function ($definition, $parent) {
                $directives = $this->builds($definition->directives, $definition);
                $operationTypes = $this->builds($definition->operationTypes, $definition);
            },
            'NamedType' => function ($definition, $parent) {
                return $this->build($definition->name, $definition);
            },
            'FieldDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $arguments = $this->builds($definition->arguments, $definition);
                $directives = $this->builds($definition->directives, $definition);
                $type = $this->build($definition->type, $definition);
            },
            'ObjectTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $fields = $this->builds($definition->fields, $definition);
                $interfaces = $this->builds($definition->interfaces, $definition);
            },
            'ScalarTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'EnumTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $values = $this->builds($definition->values, $definition);
            },
            'InterfaceTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $fields = $this->builds($definition->fields, $definition);
            },
            'InputObjectTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $fields = $this->builds($definition->fields, $definition);
            },
            'UnionTypeDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $types = $this->builds($definition->types, $definition);
            },
            'EnumValueDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'InputValueDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $description = $this->description($definition);
                $directives = $this->builds($definition->directives, $definition);
                $type = $this->build($definition->type, $definition);
                $defaultValue = $definition->defaultValue;
            },
            'OperationTypeDefinition' => function ($definition, $parent) {
                $operation = $definition->operation;
                $type = $this->build($definition->type, $definition);
            },


            'OperationDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $operation = $definition->operation;
                $selectionSet = $this->build($definition->selectionSet, $definition);
                $directives = $this->builds($definition->directives, $definition);
                $variableDefinitions = $this->builds($definition->variableDefinitions, $definition);
            },
            'FragmentDefinition' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $typeCondition = $this->build($definition->typeCondition, $definition);
                $selectionSet = $this->build($definition->selectionSet, $definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'FragmentSpread' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'InlineFragment' => function ($definition, $parent) {
                $typeCondition = $this->build($definition->typeCondition, $definition);
                $selectionSet = $this->build($definition->selectionSet, $definition);
                $directives = $this->builds($definition->directives, $definition);
            },
            'Name' => function ($definition, $parent) {
                return $definition->value;
            },
            'Argument' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $value = $this->build($definition->value, $definition);
            },
            'ListValue' => function ($definition, $parent) {
                $values = $this->builds($definition->values, $definition);
            },
            'SelectionSet' => function ($definition, $parent) {
                $selections = $this->builds($definition->selections, $definition);
            },
            'EnumValue' => function ($definition, $parent) {
                $value = $definition->value;
            },
            'Variable' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
            },
            'VariableDefinition' => function ($definition, $parent) {
                $variable = $this->build($definition->variable, $definition);
                $type = $this->build($definition->type, $definition);
                $defaultValue = $definition->defaultValue;
            },
            'Field' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $alias = $this->build($definition->alias, $definition);
                $selectionSet = $this->build($definition->selectionSet, $definition);
                $directives = $this->builds($definition->directives, $definition);
                $arguments = $this->builds($definition->arguments, $definition);
            },
            'Directive' => function ($definition, $parent) {
                $name = $this->build($definition->name, $definition);
                $arguments = $this->builds($definition->arguments, $definition);
            },
            'NonNullType' => function ($definition, $parent) {
                $type = $this->build($definition->type, $definition);
            },
            'ListType' => function ($definition, $parent) {
                $type = $this->build($definition->type, $definition);
            },
        ];
    }

    public function description($definition)
    {
        return isset($definition->description) ? trim($definition->description) : null;
    }

    public function build($node, $parent = null)
    {
        return $node === null ? null : call_user_func($this->builders[$node->kind], $node, $parent);
    }

    public function builds($nodes, $parent = null)
    {
        return array_map(function ($node) use (&$parent) {
            return $this->build($node, $parent);
        }, $nodes ?? []);
    }
}

call_user_func(function () {
    $source = <<< SOURCE
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

type Character {
  name: String!
  appearsIn: [Episode]!
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
  human(id: 1002) {
    name
    appearsIn
    starships {
      name
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

# Person description
type Person {
    a: String!
    b: [String!]
    c(a:String="a",b:Int!,c:[Boolean!]!): String
    d: [Person!]!
}

{
    name
    ... on A {
        name
        people {
            name(a:"a",b:true,c:5,d:5.5,e:null,f:[5,6,7])
        }
    }
    ... A
}

fragment A on B {
    name
}

SOURCE;


    $builder = new Builder();

    $builder->build(json_decode(json_encode(Parser::parse($source)->toArray(true))));

});
