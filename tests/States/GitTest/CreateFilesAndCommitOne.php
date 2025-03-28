<?php

namespace Test\States\GitTest;

use Test\Managers\GitManager;
use Test\States\Interfaces\FileState;
use Test\States\StateHelper;

class CreateFilesAndCommitOne implements FileState
{
    use StateHelper;

    /**
     * @var array<int, string>
     */
    private readonly array $files;

    public function __construct(
        private readonly GitManager $gitCommand
    ) {
        $this->files = [
            'migration_1.php',
            'migration_2.php',
            'migration_3.php'
        ];
    }

    public function initialize(): void
    {
        $this->createAndAdd($this->files[0]);
        $this->gitCommand->commit();

        $this->createAndAdd($this->files[1]);
        $this->createAndAdd($this->files[2]);
    }

    /**
     * @return array<int, string>
     */
    public function getExpectedFiles(): array
    {
        $files = $this->files;

        unset($files[0]);
        return array_values($files);
    }
}
