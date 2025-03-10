<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitBranchParser extends BaseParser implements Parser
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
        $output = $this->convertOutputToArray($this->output);

        $current = array_values(array_filter($output, function ($item) {
            return str_contains($item, "* ");
        }));

        return trim(str_replace('*', '', $current[0]));
    }
}
