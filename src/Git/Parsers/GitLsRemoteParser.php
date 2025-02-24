<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitLsRemoteParser implements Parser
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
        if ($this->output == '') {
            return [];
        }

        $output = explode("\n", trim($this->output));

        return array_map(function ($item) {
            $split = preg_split('/\s+/', trim($item))[1];
            return explode('/', trim($split))[2];
        }, $output);
    }
}
