<?php

namespace Test\States\GitTest;

use Test\Managers\GitManager;
use Test\States\Interfaces\FileState;
use Test\States\StateHelper;

class CreateInRemoteAndPullToLocal implements FileState
{
    use StateHelper;

    /**
     * @var array<int, string>
     */
    private readonly array $files;

    private readonly string $remoteRepository;

    public function __construct(
        private readonly GitManager $gitCommand
    ) {
        $this->files = [
            'migration_1.php'
        ];

        $this->remoteRepository = $this->gitCommand->getFileManager()->getRemoteRepository();
    }

    public function initialize(): void
    {
        // Push to remote
        $this->gitCommand->pushWithUpstream('origin', 'master');

        // Clone repo.git in the remote directory
        $this->gitCommand->cloneRepository($this->remoteRepository);

        $remotePath = $this->remoteRepository . DIRECTORY_SEPARATOR . GitManager::REPO_NAME;

        // Create file migration_2.php in branch2
        $this->createAndAdd($this->files[0], $remotePath);

        $this->gitCommand->commit($remotePath);
        $this->gitCommand->push($remotePath);

        $this->gitCommand->pull();
    }

    /**
     * @return array<int, string>
     */
    public function getExpectedFiles(): array
    {
        return $this->files;
    }
}
