<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitLsRemoteParser implements Parser
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
            $split = preg_split('/\s+/', trim($item))[1];
            return explode('/', trim($split))[2];
        }, $this->output);
    }
}
