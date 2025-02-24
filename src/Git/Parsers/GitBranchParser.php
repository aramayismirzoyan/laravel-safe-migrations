<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitBranchParser implements Parser
{
    /**
     * @param array<int, string>|string $output
     */
    public function __construct(private readonly array|string $output)
    {
    }

    /**
     * Parse Git command output
     *
     * @return string
     */
    public function parse(): string
    {
        $output = trim($this->output);

        $output = explode("\n", $output);

        $current = array_values(array_filter($output, function ($item) {
            return str_contains($item, "* ");
        }));

        return trim(str_replace('*', '', $current[0]));
    }
}
