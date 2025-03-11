<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class ListParser implements Parser
{
    /**
     * @param array<int,string> $output
     */
    public function __construct(private readonly array $output)
    {
    }

    /**
     * Parse Git command output
     *
     * @return array<int, string>|string
     */
    public function parse(): array|string
    {
        return array_map('trim', $this->output);
    }
}
