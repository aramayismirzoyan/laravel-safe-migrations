<?php

namespace Test\States\GitHubActions;

use Symfony\Component\Filesystem\Filesystem;
use Test\Managers\GitManager;
use Test\States\Interfaces\MainState;

class CreateGitHubActionsEnvironment implements MainState
{
    private string $repository;

    public function __construct(GitManager $gitCommand)
    {
        $this->repository = $gitCommand->getFileManager()->getRepository();
    }

    private function copyFiles(): void
    {
        $baseDir = dirname(__DIR__, 3);

        $files = [
            $baseDir . '/composer.json' => $this->repository . '/composer.json',
            $baseDir . '/tests/actions/php.yml' => $this->repository . '/php.yml',
            $baseDir . '/.gitignore' => $this->repository . '/.gitignore',
            $baseDir . '/tests/actions/action' => $this->repository . '/action',
        ];

        $filesystem = new Filesystem();

        foreach ($files as $source => $destination) {
            $filesystem->copy($source, $destination);
        }

        $filesystem->mirror($baseDir . '/src', $this->repository . '/src');
    }

    public function getBefore(): string
    {
        return trim(shell_exec("cd $this->repository && git log --format='%H' -n 2 | tail -n 1"));
    }

    public function getAfter(): string
    {
        return trim(shell_exec("cd $this->repository && git log --format='%H' -n 1"));
    }

    private function simulatePush(): void
    {
        $event = [
            "before" => $this->getBefore(),
            "after" => $this->getAfter(),
            "ref" => "refs/heads/master",
            "repository" => [
                "default_branch" => "master"
            ]
        ];

        file_put_contents(
            $this->repository . "/push_event.json",
            json_encode($event, JSON_PRETTY_PRINT)
        );
    }

    public function initialize(): void
    {
        $this->copyFiles();
        $this->simulatePush();
    }
}
