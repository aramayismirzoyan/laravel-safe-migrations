<?php

namespace Test\States\SafeMigrationTest;

use Test\Managers\GitManager;
use Test\States\Interfaces\MainState;
use Test\States\StateHelper;

class CreateFourFilesAndPushTwo implements MainState
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
        $this->createAndAdd($this->files[1]);

        $this->gitCommand->commit();

        $this->gitCommand->getFileManager()->create($this->files[2]);
        $this->gitCommand->getFileManager()->create($this->files[3]);

        $this->gitCommand->push();
    }
}
