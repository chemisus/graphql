<?php

namespace Chemisus\GraphQL\Types\Traits;

use Chemisus\GraphQL\KindDoesNotSupportFieldsException;
use Chemisus\GraphQL\Types\Field;

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
