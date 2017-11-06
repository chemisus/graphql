<?php

namespace Chemisus\GraphQL;

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

    public function getTypeName()
    {
        return $this->getType()->getFullName();
    }
}