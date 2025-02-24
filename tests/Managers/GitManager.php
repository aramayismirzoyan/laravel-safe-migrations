<?php

namespace Test\Managers;

use Symfony\Component\Process\Process;

class GitManager
{
    public const REPO_NAME = 'project';

    public const REPO_NAME_WITH_EXTENSION = self::REPO_NAME . '.git';

    public function __construct(private readonly FileManager $fileManager)
    {
    }

    public function initRepositoryForTesting(?string $repository = null): void
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $this->gitInit($repository);

        $this->fileManager->create('README.md', $repository);
        $this->add('README.md', $repository);
        $this->commit($repository);
    }

    public function initEmptyRepository(?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRemoteRepository();

        $process = new Process(['git', 'init', '--bare', self::REPO_NAME_WITH_EXTENSION], $repository);
        $process->run();
        return $process->isSuccessful();
    }

    public function addRemoteOrigin(
        ?string $repository = null,
        ?string $remoteOriginDirectory = null,
        ?string $origin = 'origin'
    ): bool {
        $repository = $repository ?? $this->fileManager->getRepository();
        $remoteOriginDirectory = $remoteOriginDirectory ?? $this->fileManager->getRemoteRepository();
        $fullRepoName = $remoteOriginDirectory . DIRECTORY_SEPARATOR . self::REPO_NAME_WITH_EXTENSION;

        $process = new Process(['git', 'remote', 'add', $origin, $fullRepoName], $repository);
        $process->run();
        return $process->isSuccessful();
    }

    public function getFileManager(): FileManager
    {
        return $this->fileManager;
    }

    /**
     * @param array<int, string> $files
     * @param string|null $repository
     * @return bool
     */
    public function addMultiple(array $files, ?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'add', ...$files], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function add(string $file, ?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'add', $file], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function commit(?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git',  'commit', '-m', '"test commit"'], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function push(?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'push', 'origin', 'master'], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function pushWithUpstream(string $origin, string $branch, ?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'push', '-u', $origin, $branch], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function cloneRepository(?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();
        $bareRepository = $repository . DIRECTORY_SEPARATOR . GitManager::REPO_NAME_WITH_EXTENSION;

        $process = new Process(['git', 'clone', $bareRepository], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function addBranch(string $name, ?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'checkout', '-b', $name], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function pull(?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'pull'], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function checkoutBranch(string $branch, ?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'checkout', $branch], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function merge(string $branch, ?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'merge', $branch], $repository);
        $process->run();

        return $process->isSuccessful();
    }

    public function gitInit(?string $repository = null): bool
    {
        $repository = $repository ?? $this->fileManager->getRepository();

        $process = new Process(['git', 'init'], $repository);
        $process->run();

        return $process->isSuccessful();
    }
}
