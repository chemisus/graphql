# GraphQL

- Fetch
- Type
- Resolve

```php
$gql = 'query { greeting(name:"World") }'
$schema = ...
$queryBuilder = new QueryBuilder();
$query = $queryBuilder->build($schema, $gql);
$executor = new ReactExecutor();
$data = $executor->execute($schema, $query);                        

```
    


```
interface Being {
    name: String!
}

type Person implements Being {
    name: String!
    gender: Gender!
    mother: Person!
    father: Person!
    children: [Person!]!
    husband: Person
    wife: Person
    pets: [Pet!]!
}

type Dog implements Being {
    guard: Boolean!
}

type Cat implements Being {
    lives: Integer!
}

union Pet = Dog | Cat

type Query {
    person(name:String!): Person
    people(names:[String!]!): [Person!]!
}
```
