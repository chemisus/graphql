<?php

namespace Chemisus\GraphQL;

trait NullFieldTrait
{
    public function getField(string $name): Field
    {
        return null;
    }

    public function getFields()
    {
        return [];
    }
}