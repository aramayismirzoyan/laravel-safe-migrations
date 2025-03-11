<?php

namespace Aramayismirzoyan\SafeMigrations\Git\Parsers;

interface Parser
{
    /**
     * @param array<int, string> $output
     */
    public function __construct(array $output);

    /**
     * Parse Git command output
     *
     * @return array<int, string>|string
     */
    public function parse(): array|string;
}
