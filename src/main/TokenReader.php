<?php

namespace Chemisus\GraphQL;

interface TokenReader
{
    public function readPunctuator(&$stack, &$current, $token, $tokens, &$offset);

    public function readName(&$stack, &$current, $token, $tokens, &$offset);

    public function readInt(&$stack, &$current, $token, $tokens, &$offset);

    public function readFloat(&$stack, &$current, $token, $tokens, &$offset);

    public function readString(&$stack, &$current, $token, $tokens, &$offset);
}
