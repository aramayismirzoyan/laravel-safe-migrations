<?php

namespace Test\Integration;

use Exception;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Test\States\GitHubActions\AddBranchPushAndEditFile;
use Test\States\GitHubActions\CreateAndCommitFilesOnNewBranch;
use Test\States\GitHubActions\CreateGitHubActionsEnvironment;
use Test\States\GitTest\CreateAndCommitFiles;
use Test\States\SafeMigrationTest\CommitFilePushAndEditAfter;

#[Group('actions')]
class GitHubActionsTest extends GitBase
{
    private function checkAct(): void
    {
        $act = dirname(__DIR__, 2) . '/bin/act';

        if (!file_exists($act)) {
            $exceptionText = 'Please install https://github.com/nektos/act to run GitHub Actions'
                . ' locally. You must have a bin/act folder in your project root directory.';
            throw new Exception($exceptionText);
        }
    }

    public function test_actions_when_there_are_sensitive_migrations(): void
    {
        $this->checkAct();

        // Preparing
        $state = new CommitFilePushAndEditAfter($this->gitCommand);
        $state->initialize();

        $this->gitCommand->add($state->getExpectedFiles()[0]);
        $this->gitCommand->commit();

        $stateActions = new CreateGitHubActionsEnvironment($this->gitCommand);
        $stateActions->initialize();

        // Action
        $command = dirname(__DIR__, 2) . '/bin/act -W php.yml -e push_event.json';

        $process = new Process(explode(' ', $command), $this->repository);
        $process->setTimeout(360);
        $process->run();

        $processOutput = $process->getOutput();

        // Assertion
        $this->assertStringContainsString('You have sensitive migrations.', $processOutput);
        $this->assertFalse($process->isSuccessful());
    }

    public function test_actions_when_there_are_no_sensitive_migrations(): void
    {
        $this->checkAct();

        // Preparing
        $state = new CreateAndCommitFiles($this->gitCommand);
        $state->initialize();

        $stateActions = new CreateGitHubActionsEnvironment($this->gitCommand);
        $stateActions->initialize();

        // Action
        $command = dirname(__DIR__, 2) . "/bin/act -W php.yml -e push_event.json";

        $process = new Process(explode(' ', $command), $this->repository);
        $process->setTimeout(360);
        $process->run();

        $processOutput = $process->getOutput();

        // Assertion
        $this->assertStringNotContainsString('You have sensitive migrations.', $processOutput);
        $this->assertTrue($process->isSuccessful());
    }

    public function test_actions_when_there_are_sensitive_migrations_pull_request(): void
    {
        $this->checkAct();

        // Preparing
        $state = new AddBranchPushAndEditFile($this->gitCommand);
        $state->initialize();
        $state->commitAddedFile();

        $stateActions = new CreateGitHubActionsEnvironment($this->gitCommand);
        $stateActions->initialize();

        // Action
        $command = dirname(__DIR__, 2) . '/bin/act pull_request -W php.yml -e push_event.json';

        $process = new Process(explode(' ', $command), $this->repository);
        $process->setTimeout(360);
        $process->run();

        $processOutput = $process->getOutput();

        // Assertion
        $this->assertStringContainsString('You have sensitive migrations.', $processOutput);
        $this->assertFalse($process->isSuccessful());
    }

    public function test_actions_when_there_are_no_sensitive_migrations_pull_request(): void
    {
        $this->checkAct();

        // Preparing
        $state = new CreateAndCommitFilesOnNewBranch($this->gitCommand);
        $state->initialize();

        $stateActions = new CreateGitHubActionsEnvironment($this->gitCommand);
        $stateActions->initialize();

        // Action
        $command = dirname(__DIR__, 2) . "/bin/act pull_request -W php.yml -e push_event.json";

        $process = new Process(explode(' ', $command), $this->repository);
        $process->setTimeout(360);
        $process->run();

        $processOutput = $process->getOutput();

        // Assertion
        $this->assertStringNotContainsString('You have sensitive migrations.', $processOutput);
        $this->assertTrue($process->isSuccessful());
    }
}
