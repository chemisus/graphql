<?php

namespace Chemisus\GraphQL;

class FragmentSpread implements Selection
{
    use NameTrait;
    use DirectivesTrait;

    /**
     * @var Document
     */
    private $document;

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @param Document $document
     * @return self
     */
    public function setDocument(Document $document): self
    {
        $this->document = $document;
        return $this;
    }

    public function getFragment(): Fragment
    {
        return $this->getDocument()->fragments[$this->getName()];
    }

    public function flatten(?Type $on = null)
    {
        return $this->getFragment()->flatten($on);
    }
}