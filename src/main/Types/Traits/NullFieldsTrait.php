<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\KindDoesNotSupportFieldsException;

trait NullFieldsTrait
{
    /**
     * @param string $name
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name)
    {
        throw new KindDoesNotSupportFieldsException();
    }

    /**
     * @return null
     */
    public function fields()
    {
        return null;
    }
}
