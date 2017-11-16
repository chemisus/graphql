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

## Document Wiring

So now we know why the need for fetch and resolve phases, but we still need to
specify what they actually do for an application. This is where wirings come in.

There are two categories of wirings: nodes and edges.

#### Node Wirings

```php
Typer::type(Node $node, $value);
Document::typer(Typer $typer);
```

```php
Coercer::coerce(Node $node, $value);
Document::coercer(Coercer $coercer);
```

#### Edge Wirings

```php
Fetcher::fetch(Node $node);
Document::fetcher(Fetcher $fetcher);
```

```php
Resolver::resolve(Node $node, $parent, $value);
Document::resolver(Resolver $resolver);
```

### Wiring Example

```
type Query {
    book(id:String!): Book!
    books(ids:[String!]!): [Book!]!
}

type Book {
    id: String!
    title: String!
    authorId: String!
    author: Person!
}

type Person {
    id: String!
    name: String!
}
```

###### Node: BookWirer

```php
class BookWirer implements Wirer {
    function wire(Document $document) {
        $document->coerce('Book', new CallbackCoercer(function (Node $node, Book $value) {
            return (object)[
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'authorId' => $book->getAuthorId(),
            ];
        });
    }
}
```

###### Node: PersonWirer

```php
class PersonWirer implements Wirer {
    function wire(Document $document) {
        $document->coerce('Person', new CallbackCoercer(function (Node $node, Person $value) {
            return (object)[
                'id' => $person->getId(),
                'name' => $person->getName(),
            ];
        });
    }
}
```

###### Edge: QueryBookWirer

```php
class QueryBookWirer implements Wirer {
    function wire(Document $document) {
        $document->fetcher('Query', 'book', new CallbackFetcher(function (Node $node) {
            $ids = $node->arg('id');
            return [BookRepository::getBook($id)];
        });

        $document->resolve('Query', 'book', new CallbackResolver(function (Node $node, $parent, $value) {
            $books = $node->getItems();
            return array_shift($books);
        });
    }
}
```

###### Edge: QueryBooksWirer

```php
class QueryBooksWirer implements Wirer {
    function wire(Document $document) {
        $document->fetcher('Query', 'books', new CallbackFetcher(function (Node $node) {
            $ids = $node->arg('ids', []);
            return count($ids) ? BookRepository::getBooks($ids) : [];
        });

        $document->resolve('Query', 'books', new CallbackResolver(function (Node $node, $parent, $value) {
            return $node->getItems();
        });
    }
}
```

###### Edge: BookAuthorWirer

```php
class BookAuthorWirer implements Wirer {
    function wire(Document $document) {
        $document->fetch('Book', 'author', new CallbackFetcher(function (Node $node) {
            $mapBookToAuthorId = function (Book $book) {
                return $book->getAuthorId();
            };
            $ids = array_map($mapBookToAuthorId, $node->getParent()->getItems());
            return count($ids) ? PersonRepository::getPersons($ids) : [];
        });

        $document->resolve('Book', 'author', new CallbackResolver(function (Node $node, Book $book, $value) {
            $filterAuthorById = function ($authorId) {
                return function (Person $person) use ($authorId) {
                    return $person->id === $authorId;
                };
            };
            $authors = array_filter($node->getParent()->getItems(), $filterAuthorById($book->getAuthorId())
            return $node->getItems();
        });
    }
}
```

## Document Execution

```php
class GraphQLRunner {
    /**
     * @param string $source
     * @param array $variables
     * @param Wirer[] $wirers
     */
    public function run (
        string $source,
        array $queryVariables,
        $wirers
    ) {
        // 1. load
        $documentBuilder = new DocumentBuilder();
        $documentBuilder->loadSource($source);
        $documentBuilder->loadVariables($queryVariables)
        
        // 2. build
        $document = $documentBuilder->buildDocument();
    
        // 3. wire
        $introspectionWirer = new IntrospectionWirer();
        $introspectionWirer->wire($document);
    
        foreach($wirers as $wirer) {
            $wirer->wire($document);
        }    
    
        // 4. execute    
        $documentExecutor = new DocumentExecutor();
        $data = $documentExecutor->execute($document);
        
        return $data;
    }
}
```

```php
$schemaSource = "type Query { ... }";
$querySource = "query Query { ... }";
$source = implode(PHP_EOL, [$schemaSource, $querySource]);

$variables = ['param1' => 'value1'];
$wirers = [
    new BookWirer(),
    new PersonWirer(),
    new QueryBookWirer(),
    new QueryBooksWirer(),
    new BookAuthorWirer(),
];

$graphql = new GraphQLRunner();
$data = $graphql->run($source, $variables, $wirers)
```

## Extensions

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
