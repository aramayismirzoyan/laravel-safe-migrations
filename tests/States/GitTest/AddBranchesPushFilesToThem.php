<?php

namespace Test\States\GitTest;

use Test\Managers\GitManager;
use Test\States\Interfaces\MainState;
use Test\States\StateHelper;

class AddBranchesPushFilesToThem implements MainState
{
    use StateHelper;
    private readonly string $repository;
    private readonly string $remoteRepository;

    public function __construct(private readonly GitManager $gitCommand)
    {
        $this->repository = $this->gitCommand->getFileManager()->getRepository();
        $this->remoteRepository = $this->gitCommand->getFileManager()->getRemoteRepository();
    }

    public function initialize(): void
    {
        $this->gitCommand->pushWithUpstream('origin', 'master');

        $this->gitCommand->cloneRepository($this->remoteRepository);

        $remotePath = $this->remoteRepository . DIRECTORY_SEPARATOR . GitManager::REPO_NAME;

        // Create branch2
        $this->gitCommand->addBranch('branch2', $remotePath);
        $this->createAndAdd('index.php', $remotePath);
        $this->gitCommand->commit($remotePath);
        $this->gitCommand->pushWithUpstream('origin', 'branch2', $remotePath);

        // Create branch3
        $this->gitCommand->addBranch('branch3', $remotePath);
        $this->createAndAdd('index2.php', $remotePath);
        $this->gitCommand->commit($remotePath);
        $this->gitCommand->pushWithUpstream('origin', 'branch3', $remotePath);

        $this->gitCommand->addRemoteOrigin($this->repository, $this->remoteRepository, 'local');
    }
}
