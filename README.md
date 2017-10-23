# GraphQL

- Fetch
- Type
- Resolve

```
for each $node in $queue
    fetch $items for $node
        for each $item in $items
            determine $type for $item
                        

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
