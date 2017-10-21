<?php

namespace GraphQL;

interface Type
{
    /**
     * @param string $name
     * @return Field
     */
    public function field(string $name);
}
