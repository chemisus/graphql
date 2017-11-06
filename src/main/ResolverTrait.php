<?php

namespace Chemisus\GraphQL;

trait ResolverTrait
{
    /**
     * @var Resolver
     */
    private $resolver;

    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }
}