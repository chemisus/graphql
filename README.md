[![Build Status](https://travis-ci.org/chemisus/graphql.svg?branch=master)](https://travis-ci.org/chemisus/graphql)

`composer require chemisus/graphql`

# GraphQL

If you're looking for a reactive, two phase, BFS, graphql library, then you've found it!

> ***What do you mean by two phases?***

The execution of a query consists of two phases: fetch and resolve. 

#### Fetch Phase

The primary goal of the fetch phase is to try retrieve any data necessary to complete 
the query, efficiently in terms of connections and/or data transfer. Any items that 
are fetched will be stored on the node, and can then be referenced by child nodes during 
their fetch process, or the node itself during its resolve process.

The fetch phase is executed in a BFS manner. 

The fetch phase utilizes [ReactPHP](https://reactphp.org), allowing a node to return 
promises. If a promise is returned, child nodes will not begin their fetch until the 
promise has been resolved. 

###### HTTP

A *[very basic](https://github.com/chemisus/graphql/issues/9)* HTTP helper is included 
that provides non-blocking, allowing nodes to process while other nodes fetch data. 
Each method returns a promise.

###### SQL

Unfortunately, PDO (and by extension any library that relies on it) does not support 
non-blocking queries. It appears possible to get the old-school mysqli library to work 
with non-blocking queries. [Issue #10](https://github.com/chemisus/graphql/issues/10) 
was made to look into it.

#### Resolve Phase

Once the fetch phase is complete, the resolve phase will then assemble the result by 
allowing each node to generate, or select its value(s) from the fetched data.

The resolve phase is executed in a DFS manner. 

While resolving does not currently 
support promises or callbacks being returned, 
[issue #5](https://github.com/chemisus/graphql/issues/5) is tracking the progress.

## Wiring

* Document::coercer(Coercer $coercer)
* Document::typer(Typer $typer)
* Document::fetcher(Fetcher $fetcher)
* Document::resolver(Resolver $resolver)

## Document Stages

The following are the four main stages and their substages that will get you from 
start to finish:

1. load document
    1. source
    2. variables
2. build document
    1. parse source
    2. build schema
    3. build query
3. wire document
    1. introspection
    2. application
4. execute document
    1. fetch phase
    2. resolve phase

##### Example (version: 1.1)

```php
function generateGraphData(
    string $schemaSource,
    string $querySource,
    array $queryVariables,
    \Chemisus\GraphQL\Wirer $applicationWirer
) {
    // 1. load
    $documentBuilder = new \Chemisus\GraphQL\DocumentBuilder();
    $documentBuilder->loadVariables($queryVariables)
    $documentBuilder->loadSource($schemaSource);
    $documentBuilder->loadSource($querySource);
    
    // 2. build
    $document = $documentBuilder->buildDocument();

    // 3. wire
    $introspectionWirer = new \Chemisus\GraphQL\IntrospectionWirer();
    $introspectionWirer->wire($document);

    $applicationWirer->wire($document);

    // 4. execute    
    $documentExecutor = new \Chemisus\GraphQL\DocumentExecutor();
    $data = $documentExecutor->execute($document);
    
    return $data;
}
```

### Load Stage

```php
    $documentBuilder = new \Chemisus\GraphQL\DocumentBuilder();
    $documentBuilder->loadVariables($queryVariables)
    $documentBuilder->loadSource($schemaSource);
    $documentBuilder->loadSource($querySource);
```

### Build Stage

```php
    $document = $builder->buildDocument();
```

```php
    $documentBuilder->parse();
    $documentBuilder->buildSchema();
    $documentBuilder->buildOperations();
    $document = $documentBuilder->document();
```

### Wire Stage

```php
    $introspectionWirer = new \Chemisus\GraphQL\IntrospectionWirer();
    $introspectionWirer->wire($document);

    $applicationWirer->wire($document);
```

### Execute Stage

```php
    $documentExecutor = new \Chemisus\GraphQL\DocumentExecutor();
    $data = $documentExecutor->execute($document);
```

## Introspection Extensions

```
extend type __Type {
    # returns type name with modifications (e.g. "[ID!]!" -> "[ID!]!")
    fullName: String!

    # returns type name with no modifications  (e.g. "[ID!]!" -> "ID")
    baseName: String!
}

extend type __Field {
    # returns full name of field type (e.g. "[ID!]!" -> "[ID!]!")
    typeName: String!
}
```

## Requirements

- php 7.1
