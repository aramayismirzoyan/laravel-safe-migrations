<?php

namespace Test\States\GitHubActions;

use Test\Managers\GitManager;
use Test\States\Interfaces\FileState;
use Test\States\StateHelper;

class CreateAndCommitFilesOnNewBranch implements FileState
{
    use StateHelper;

    public const NEW_BRANCH_NAME = 'branch2';

    /**
     * @var array<int, string>
     */
    private readonly array $files;

    public function __construct(private readonly GitManager $gitCommand)
    {
        $this->files = [
            'migration_1.php',
            'migration_2.php',
            'migration_3.php'
        ];
    }

    public function initialize(): void
    {
        $this->gitCommand->pushWithUpstream('origin', 'master');

        $this->gitCommand->addBranch(self::NEW_BRANCH_NAME);

        $this->gitCommand->pushWithUpstream('origin', self::NEW_BRANCH_NAME);

        $this->createAndAdd($this->files[0]);
        $this->createAndAdd($this->files[1]);
        $this->createAndAdd($this->files[2]);
        $this->gitCommand->commit();
    }

    /**
     * @return array<int, string>
     */
    public function getExpectedFiles(): array
    {
        return $this->files;
    }
}
