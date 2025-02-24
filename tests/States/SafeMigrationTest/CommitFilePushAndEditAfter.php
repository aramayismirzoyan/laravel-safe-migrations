<?php

namespace Test\States\SafeMigrationTest;

use Test\Managers\GitManager;
use Test\States\Interfaces\FileState;
use Test\States\StateHelper;

class CommitFilePushAndEditAfter implements FileState
{
    use StateHelper;

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

        $this->gitCommand->getFileManager()->editFile($this->files[0]);

    }

    /**
     * @return array<int, string>
     */
    public function getExpectedFiles(): array
    {
        return $this->files;
    }
}
