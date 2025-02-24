<?php

namespace Aramayismirzoyan\SafeMigrations\Git;

class GitHelper
{
    /**
     * Create branch remote pairs
     *
     * @param array<int, string> $remotes
     * @param array<int, string> $branches
     * @return array<int, string>
     */
    public function collectRemotes(array $remotes, array $branches): array
    {
        return collect($remotes)
            ->crossJoin($branches)
            ->map(function (array $item) {
                return $item[0] . '/' . $item[1];
            })->toArray();
    }
}
