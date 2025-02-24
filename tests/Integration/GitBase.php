<?php

namespace Test\Integration;

use PHPUnit\Framework\TestCase;
use Test\Managers\FileManager;
use Test\Managers\GitManager;

class GitBase extends TestCase
{
    protected string $repository;
    protected string $remoteRepository;

    protected FileManager $file;

    protected GitManager $gitCommand;

    public function setUp(): void
    {
        $this->file = new FileManager();
        $this->repository = $this->file->getRepository();
        $this->remoteRepository = $this->file->getRemoteRepository();

        $this->gitCommand = new GitManager($this->file);
        $this->gitCommand->initRepositoryForTesting();
        $this->gitCommand->initEmptyRepository();
        $this->gitCommand->addRemoteOrigin($this->repository, $this->remoteRepository);
    }

    public function tearDown(): void
    {
        $this->file->removeTestDirectories();
    }
}
