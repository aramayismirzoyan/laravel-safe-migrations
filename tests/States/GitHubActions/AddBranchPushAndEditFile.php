<?php

namespace Test\States\GitHubActions;

use Test\Managers\GitManager;
use Test\States\Interfaces\FileState;
use Test\States\StateHelper;

class AddBranchPushAndEditFile implements FileState
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
            'migration_1.php'
        ];
    }

    public function initialize(): void
    {
        $this->gitCommand->pushWithUpstream('origin', 'master');

        $this->createAndAdd($this->files[0]);
        $this->gitCommand->commit();
        $this->gitCommand->push();

        $this->gitCommand->addBranch(self::NEW_BRANCH_NAME);

        $this->gitCommand->getFileManager()->editFile($this->files[0]);
    }

    public function getExpectedFiles(): array
    {
        return $this->files;
    }

    public function commitAddedFile(): void
    {
        $this->gitCommand->add($this->files[0]);
        $this->gitCommand->commit();
    }
}
