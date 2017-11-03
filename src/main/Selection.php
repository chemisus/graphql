<?php

namespace Chemisus\GraphQL;

interface Selection
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return string
     */
    public function alias(): string;

    /**
     * @return string|null
     */
    public function on(): ?string;

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function arg(string $key, $default = null);

    /**
     * @return array|null
     */
    public function args(): ?array;

    /**
     * @param string $on
     * @return Selection[]
     */
    public function fields(?string $on = null);

    /**
     * @param string $on
     * @return Selection[]
     */
    public function flatten(?string $on = null);

    /**
     * @return Selection[]
     */
    public function selections();
}