<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitBranchParser implements Parser
{
    /**
     * @param array<int, string> $output
     */
    public function __construct(private readonly array $output)
    {
    }

    /**
     * Parse Git command output
     *
     * @return string
     */
    public function parse(): string
    {
        $current = array_values(array_filter($this->output, function ($item) {
            return str_contains($item, "* ");
        }));

        return trim(str_replace('*', '', $current[0]));
    }
}
