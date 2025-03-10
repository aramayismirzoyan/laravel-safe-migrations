<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class ListParser extends BaseParser implements Parser
{
    /**
     * @param array<int,string>|string $output
     */
    public function __construct(private readonly array|string $output)
    {
    }

    /**
     * Parse Git command output
     *
     * @return array<int, string>|string
     */
    public function parse(): array|string
    {
        $output = $this->convertOutputToArray($this->output);

        return array_map('trim', $output);
    }
}
