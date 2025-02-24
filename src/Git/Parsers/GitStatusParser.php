<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitStatusParser implements Parser
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

        $result = trim($this->output);
        $result = explode("\n", $result);

        return array_map(function ($item) {
            return basename(preg_split('/\s+/', trim($item))[1]);
        }, $result);
    }
}
