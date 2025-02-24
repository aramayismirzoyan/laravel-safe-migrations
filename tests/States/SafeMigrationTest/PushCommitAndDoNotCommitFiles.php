<?php

namespace Test\States\SafeMigrationTest;

use Test\Managers\GitManager;
use Test\States\Interfaces\FileState;
use Test\States\StateHelper;

class PushCommitAndDoNotCommitFiles implements FileState
{
    use StateHelper;

    /**
     * @var array<int, string>
     */
    private readonly array $files;

    public function __construct(private readonly GitManager $gitCommand)
    {
        $this->files = [
            'migration_1.php',
            'migration_2.php',
            'migration_3.php',
            'migration_4.php',
        ];
    }

    public function initialize(): void
    {
        $this->gitCommand->pushWithUpstream('origin', 'master');

        $this->createAndAdd($this->files[0]);
        $this->gitCommand->commit();
        $this->gitCommand->push();

        $this->gitCommand->getFileManager()->editFile($this->files[0]);
        $this->gitCommand->add($this->files[0]);
        $this->gitCommand->commit();

        $this->createAndAdd($this->files[1]);
        $this->gitCommand->commit();

        $this->createAndAdd($this->files[2]);

        $this->gitCommand->getFileManager()->create($this->files[3]);
    }

    /**
     * @return array<int, string>
     */
    public function getExpectedFiles(): array
    {
        return [$this->files[0]];
    }
}
