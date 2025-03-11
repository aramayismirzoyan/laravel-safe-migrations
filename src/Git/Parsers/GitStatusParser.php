<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitStatusParser implements Parser
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
            return basename(preg_split('/\s+/', trim($item))[1]);
        }, $this->output);
    }
}
