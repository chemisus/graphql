<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\Field;
use Chemisus\GraphQL\KindDoesNotSupportFieldsException;

trait NullFieldsTrait
{
    /**
     * @param string $name
     * @return Field
     * @throws KindDoesNotSupportFieldsException
     */
    public function field(string $name): Field
    {
        throw new KindDoesNotSupportFieldsException();
    }

    /**
     * @return Field[]|null
     */
    public function fields(): ?array
    {
        return null;
    }
}
