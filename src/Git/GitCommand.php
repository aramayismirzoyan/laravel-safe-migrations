<?php

namespace Aramayismirzoyan\SafeMigrations\Git;

use Aramayismirzoyan\SafeMigrations\Command\Runner;
use Aramayismirzoyan\SafeMigrations\Expressions\GitException;

class GitCommand
{
    /**
     * @param string $repository
     */
    public function __construct(private readonly string $repository)
    {
    }

    /**
     * Fetch all branches
     *
     * @return void
     * @throws GitException
     */
    public function fetchAll(): void
    {
        $command = "git fetch --all";
        $runner = (new Runner($command, $this->repository))->run();

        if (!$runner->isSuccessful()) {
            throw new GitException('Git fetch command is not successful');
        }
    }
}
