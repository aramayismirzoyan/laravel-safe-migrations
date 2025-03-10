<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class BaseParser
{
    /**
     * @param string $output
     * @return array<int, string>
     */
    protected function convertOutputToArray(string $output): array
    {
        return explode("\n", trim($output));
    }
}