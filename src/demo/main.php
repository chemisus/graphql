<?php

namespace Chemisus\GraphQL;

use GraphQL\Language\Parser;

error_reporting(E_ALL);

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

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

    $builder = new GraphQLSchemaBuilder();

    $schema = $builder->buildSchema(json_decode(json_encode(Parser::parse($source)->toArray(true))));

    echo $schema;
});
