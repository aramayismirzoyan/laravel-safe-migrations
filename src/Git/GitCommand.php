<?php

namespace Aramayismirzoyan\SafeMigrations\Git;

use Aramayismirzoyan\SafeMigrations\Expressions\GitException;
use Symfony\Component\Process\Process;

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
        $process = new Process(['git', 'fetch', '--all'], $this->repository);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new GitException('Git fetch command is not successful');
        }
    }
}
