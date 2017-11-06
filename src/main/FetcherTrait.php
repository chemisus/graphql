<?php

namespace Chemisus\GraphQL;

trait FetcherTrait
{
    /**
     * @var Fetcher
     */
    private $fetcher;

    public function setFetcher(Fetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }
}