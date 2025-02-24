<?php

namespace Test\States\GitTest;

use Test\Managers\GitManager;
use Test\States\Interfaces\FileState;
use Test\States\StateHelper;

class CommitFilesAndAddTwoOrigins implements FileState
{
    use StateHelper;

    /**
     * @var array<int, string>
     */
    private readonly array $files;

    private readonly string $thirdRemote;

    public function __construct(private readonly GitManager $gitCommand)
    {
        $this->files = [
            'migration_1.php',
            'migration_2.php',
            'migration_3.php'
        ];

        $this->thirdRemote = $this->gitCommand->getFileManager()->createDirectory();
    }

    public function initialize(): void
    {
        $this->gitCommand->pushWithUpstream('origin', 'master');

        $this->createAndAdd($this->files[0]);
        $this->createAndAdd($this->files[1]);
        $this->createAndAdd($this->files[2]);

        $this->gitCommand->commit();

        // create second remote


        $this->gitCommand->initEmptyRepository($this->thirdRemote);
        $localRepository = $this->gitCommand->getFileManager()->getRepository();
        $this->gitCommand->addRemoteOrigin($localRepository, $this->thirdRemote, 'local');

        $this->gitCommand->pushWithUpstream('local', 'master');
    }

    /**
     * @return array<int, string>
     */
    public function getExpectedFiles(): array
    {
        return $this->files;
    }

    public function removeThirdRemote(): void
    {
        $this->gitCommand->getFileManager()->removeDirectory($this->thirdRemote);
    }
}
