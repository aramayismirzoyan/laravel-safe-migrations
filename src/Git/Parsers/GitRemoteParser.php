<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitRemoteParser implements Parser
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
     * @return array<int, string>
     */
    public function parse(): array
    {
        $result = explode("\n", trim($this->output));

        return array_map(function ($item) {
            return trim($item);
        }, $result);
    }
}
