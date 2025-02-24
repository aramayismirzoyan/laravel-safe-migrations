<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

class GitDiffParser implements Parser
{
    /**
     * @param array<int,string>|string $output
     */
    public function __construct(private readonly array|string $output)
    {
    }

    /**
     * @return array<int, string>
     */
    public function parse(): array
    {
        if ($this->output == '') {
            return [];
        }

        $output = array_filter($this->output, function ($item) {
            if ((str_contains($item, "files changed")
                    || str_contains($item, "file changed"))
                && (str_contains($item, "insertions")
                    || str_contains($item, "insertion"))) {
                return false;
            }

            return true;
        });

        return array_map(function ($item) {
            return basename(preg_split('/\s+/', trim($item))[0]);
        }, $output);
    }
}
