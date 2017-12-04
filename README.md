[![Build Status](https://travis-ci.org/chemisus/graphql.svg?branch=master)](https://travis-ci.org/chemisus/graphql)

`composer require chemisus/graphql`

# GraphQL

If you're looking for a reactive, two phase, BFS, graphql library<sup>†</sup>, then you've found it!

> ***What do you mean by "two phases"?***

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
allowing each node to generate, or select its value(s) for the final result.

The resolve phase is executed in a DFS manner. 

While resolving does not currently 
support promises or callbacks being returned, 
[issue #5](https://github.com/chemisus/graphql/issues/5) is tracking the progress.

## Nodes

A `Node` will contain combined information that is useful when wiring a document.

* `Node::getDocument()` gets the document itself
* `Node::arg(string $key, $default=null)` gets an argument if it was specified, $default otherwise.
* `Node::args()` gets all arguments that were specified.
* `Node::getSelection()` gets all fields specified
* `Node::getParent()` gets the parent node
* `Node::getItems()` gets items that were fetched for the node

## Document Wiring

So now we know why the need for fetch and resolve phases, but we still need to
specify what they actually do for an application. This is where wirings come in.

A document has four wire operations, each of which can be placed into one of the
two following categories: nodes and edges.

#### Fetcher

`Document::fetch(Fetcher $fetcher)` adds a fetcher to the document. A fetcher is
an edge operation, and as previously discussed, will allow fetching data in bulk. 
The value returned by a fetcher should be an array, even if the node type itself 
is not a list. The items returned by the fetcher will be available in that node's 
`Node::getItems()`. During execution, for each time an edge is specified in
a query, the edge's fetcher will be called once. Each time the fetcher is 
called, the node provided could have different items, arguments, or children. 

#### Resolver

`Document::resolve(Resolver $resolver)` adds a resolver to the document. A 
resolver is an edge operation, and as previously discussed, will determine the 
final result for an edge.

#### Coercer

`Document::coerce(Coercer $coercer)` adds a coercer to the document. A coercer is 
a node operation that will translate a node's value into a json value. The value 
returned by `Coercer::coerce(Node $node, $value)` should be a mixed value that 
follows the schema's definition for that node. 

If the node is an object, then it helps to think of the coercer as a great way to
specify multiple resolvers. The value returned by the coercer should be an object 
containing at a minimum the fields that do not have resolvers defined for them. 
The values for the object themselves do not need to be coerced, as they will be 
coerced later if they were specified in the query.

#### Typer

`Document::type(Typer $typer)` adds a typer to the document. A typer is a node
operation that will determine the concrete type of a value, if the node's type
is an interface or union. The value returned by `Typer::type(Node $node, $value)`
should be an instance of a Type, which can be obtained by 
`$node->getDocument()->getType($name)`;

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

These extensions are provided by default, however, might be moved to their own plugin
package at a later date.

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

## Development & Testing

This package can setup a [docker](https://docker.com) container 
using [docker-compose](https://docs.docker.com/compose/) for its 
testing environment.

##### Docker Environment

`ant` will do everything needed to run tests, including setting up
the docker container.

##### Host Environment

`ant -Dcontained=true` will do the same, but on the host instead.

#### Testing

There are two main test runners: `SchemaTest` and `ErrorsTest`.

##### SchemaTest

`SchemaTest` will load a schema, run queries against it, then compare
the actual result to expected results.

To add a schema, create a file at 
`./resources/test/schema/<schemaName>.gql`.

To add a query for a schema, create the files
`./resources/test/schema/<schemaName>/<queryName>.gql` for the query, and
`./resources/test/schema/<schemaName>/<queryName>.json` for the results.

To add a wirer for a schema, create a Wirer class at
`./src/test/Wirers/<schemaName>DocumentWirer.php`

`SchemaTest` will detect the files once created, and load them automatically.

##### ErrorsTest

`ErrorsTest` is to test the various errors that graphql specifies.

```
†: in php, of course
```
