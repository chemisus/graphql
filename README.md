# GraphQL

[![Build Status](https://travis-ci.org/chemisus/graphql.svg?branch=master)](https://travis-ci.org/chemisus/graphql)

## Requirements

- php 7.1

## Install

`composer require chemisus/graphql`

## Use

##### Example

```php
function graph(
    string $schemaSource,
    string $querySource,
    array $queryVariables,
    \Chemisus\GraphQL\Wirer $schemaWirer
) {
    // load
    $documentBuilder = new \Chemisus\GraphQL\DocumentBuilder();
    $documentBuilder->loadVariables($queryVariables)
    $documentBuilder->loadSource($schemaSource);
    $documentBuilder->loadSource($querySource);
    
    // build
    $document = $documentBuilder->buildDocument();

    // wire
    $introspectionWirer = new \Chemisus\GraphQL\IntrospectionWirer();
    $introspectionWirer->wire($document);

    $schemaWirer->wire($document);

    // execute    
    $documentExecutor = new \Chemisus\GraphQL\DocumentExecutor();
    $data = $documentExecutor->execute($document);
    
    return $data;
}
```

### Load

```php
    $documentBuilder = new \Chemisus\GraphQL\DocumentBuilder();
    $documentBuilder->loadVariables($queryVariables)
    $documentBuilder->loadSource($schemaSource);
    $documentBuilder->loadSource($querySource);
```

### Build

```php
    $document = $builder->buildDocument();
```

```php
    $documentBuilder->parse();
    $documentBuilder->buildSchema();
    $documentBuilder->buildOperations();
    $document = $documentBuilder->document();
```

### Wire

```php
    $introspectionWirer = new \Chemisus\GraphQL\IntrospectionWirer();
    $introspectionWirer->wire($document);

    $schemaWirer->wire($document);
```

### Execute

```php
    $documentExecutor = new \Chemisus\GraphQL\DocumentExecutor();
    $data = $documentExecutor->execute($document);
```

#### Fetch Phase

#### Resolve Phase

## Introspection Extensions

```
extend type __Type {
    # returns type name with modifications (e.g. [String!]!)
    fullName: String!

    # returns type name with no modifications
    baseName: String!
}

extend type __Field {
    # returns full name of field type
    typeName: String!
}
```

##### Example
```
Query:
{
    __type(name:"PersonPlanetInterface") {
        fields {
            typeName
            type {
                fullName
                baseName
            }
        }
    }
}

Response:
{
  "PersonPlanetInterface": {
    "fields": [
      {
        "name": "id",
        "typeName": "ID!",
        "type": {
          "fullName": "ID!",
          "baseName": "ID"
        }
      },
      {
        "name": "name",
        "typeName": "String",
        "type": {
          "fullName": "String",
          "baseName": "String"
        }
      }
    ]
  }
}

```
