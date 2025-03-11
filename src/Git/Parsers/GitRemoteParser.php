<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitRemoteParser implements Parser
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
     * @return array<int, string>
     */
    public function parse(): array
    {
        return array_map(function ($item) {
            return trim($item);
        }, $this->output);
    }
}
