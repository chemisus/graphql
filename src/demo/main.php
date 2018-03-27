<?php

namespace Chemisus\GraphQL;

use Chemisus\GraphQL\Resolvers\AllFetchedItemsResolver;
use Chemisus\GraphQL\Wirers\BasicDocumentWirer;

error_reporting(E_ALL);

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

function executeQuery(string $schema, string $query, array $variables = [])
{
    $builder = new DocumentBuilder();
    $builder->loadSource($query);
    $builder->loadSource($schema);
    $builder->loadVariables($variables);

    $document = $builder->buildDocument();
    $wirer = new IntrospectionDocumentWirer();
    $wirer->wire($document);

    $document->resolver('Query', 'hello', new class implements Resolver
    {
        public function resolve(Node $node, $parent, $value)
        {
            return sprintf('Hello, %s!', $node->arg('name', 'World'));
        }
    });

    /*
     * Fetcher for Query.books
     *
     * This fetcher simulates selecting books by id from a database.
     */
    $document->fetcher('Query', 'books', new class implements Fetcher
    {
        private $books;

        public function __construct()
        {
            $this->books = [
                'a' => (object)['id' => 'a', 'name' => 'A'],
                'b' => (object)['id' => 'b', 'name' => 'B'],
                'c' => (object)['id' => 'c', 'name' => 'C'],
                'd' => (object)['id' => 'd', 'name' => 'D'],
                'e' => (object)['id' => 'e', 'name' => 'E'],
            ];
        }

        public function fetch(Node $node, $parent)
        {
            $ids = (array)$node->arg('ids');

            printf("FETCHING Books (%s)\n", json_encode($ids));

            // Ideally this would be some sort result from a database.
            // Instead, were just pulling from the data stored locally.
            return array_values(array_intersect_key($this->books, array_flip($ids)));
        }
    });

    /*
     * Resolver for Query.books
     *
     * Since the parent type is Query, its most likely safe to just return all items that were fetched
     * by the Query.books fetcher.
     */
    $document->resolver('Query', 'books', new AllFetchedItemsResolver());

    /*
     * Fetcher for Book.chapters
     *
     * Fetches all chapters for all books that were fetched in the parent node.
     * The fetcher should ideally fetch all data that is necessary for the node resolver,
     * and should attempt to do so in the least number of connections possible.
     *
     * $books would be ALL of the books fetched by the parent node.
     */
    $document->fetcher('Book', 'chapters', new class implements Fetcher
    {
        private $chapters;

        public function __construct()
        {
            $this->chapters = [
                'a.1' => (object)['id' => 'a.1', 'book_id' => 'a', 'name' => 'A.1'],
                'a.2' => (object)['id' => 'a.2', 'book_id' => 'a', 'name' => 'A.2'],
                'a.3' => (object)['id' => 'a.3', 'book_id' => 'a', 'name' => 'A.3'],
                'b.1' => (object)['id' => 'b.1', 'book_id' => 'b', 'name' => 'B.1'],
                'b.2' => (object)['id' => 'b.2', 'book_id' => 'b', 'name' => 'B.2'],
                'b.3' => (object)['id' => 'b.3', 'book_id' => 'b', 'name' => 'B.3'],
                'c.1' => (object)['id' => 'c.1', 'book_id' => 'c', 'name' => 'C.1'],
                'c.2' => (object)['id' => 'c.2', 'book_id' => 'c', 'name' => 'C.2'],
                'c.3' => (object)['id' => 'c.3', 'book_id' => 'c', 'name' => 'C.3'],
                'd.1' => (object)['id' => 'd.1', 'book_id' => 'd', 'name' => 'D.1'],
                'd.2' => (object)['id' => 'd.2', 'book_id' => 'd', 'name' => 'D.2'],
                'd.3' => (object)['id' => 'd.3', 'book_id' => 'd', 'name' => 'D.3'],
                'e.1' => (object)['id' => 'e.1', 'book_id' => 'e', 'name' => 'E.1'],
                'e.2' => (object)['id' => 'e.2', 'book_id' => 'e', 'name' => 'E.2'],
                'e.3' => (object)['id' => 'e.3', 'book_id' => 'e', 'name' => 'E.3'],
            ];
        }

        public function fetch(Node $node, $books)
        {
            // Get the ids for the books
            $bookIds = array_unique(array_map(function ($book) {
                return $book->id;
            }, $books));

            printf("FETCHING Chapters for books (%s)\n", json_encode($bookIds));

            // Get all chapters that belong to the books
            return array_values(array_filter($this->chapters, function ($chapter) use ($bookIds) {
                return in_array($chapter->book_id, $bookIds);
            }));
        }
    });

    /*
     * Resolver for Book.chapters
     *
     * Since the Book.chapter fetcher gets the chapters for multiple books at a time, it is up
     * to the resolver to determine which chapters belong to a book.
     */
    $document->resolver('Book', 'chapters', new class implements Resolver
    {
        public function resolve(Node $node, $book, $value)
        {
            // Since we do not expect books to have been fetched with chapters, we will use the
            // data fetched for the node.
            $allChapters = $node->getItems();

            return array_filter($allChapters, function ($chapter) use ($book) {
                return $chapter->book_id === $book->id;
            });
        }
    });

    echo "\n=====================================================\n";
    echo "Query: \n{$query}\n\nFetches:\n";

    $executor = new DocumentExecutor();
    $result = $executor->execute($document);

    echo "\nResult:\n";

    echo \GuzzleHttp\json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

$schema = <<< SCHEMA
schema {
    query: Query
}

type Query {
    hello(name: String="World"): String!
    books(ids: [ID!]!): [Book]!
}

type Book {
    id: ID!
    name: String!
    chapters: [Chapter!]!
}

type Chapter {
    id: ID!
    name: String!
    bookId: ID!
}

extend type Query {
    __schema: __Schema!
    __type: __Type!
}

SCHEMA;

call_user_func(function (string $schema) {
    $queries = [
        'query { hello, helloWorld:hello, helloAlice:hello(name:"Alice") }',
        'query { hello, books(ids:["a", "c"]) { id name } }',
        'query { hello, books(ids:["a", "c"]) { id name chapters { id name } } }',
    ];

    foreach ($queries as $query) {
        executeQuery($schema, $query);
    }
}, $schema);
