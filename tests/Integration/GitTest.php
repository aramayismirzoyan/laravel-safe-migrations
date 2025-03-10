<?php

namespace Test\Integration;

use Aramayismirzoyan\SafeMigrations\Expressions\NotValidCommitHashException;
use Aramayismirzoyan\SafeMigrations\Git\GitQuery;
use Symfony\Component\Process\Process;
use Test\States\GitHubActions\CreateGitHubActionsEnvironment;
use Test\States\GitTest\AddBranchesPushFilesToThem;
use Test\States\GitTest\CommitFilesAndAddTwoOrigins;
use Test\States\GitTest\CreateAndCommitFiles;
use Test\States\GitTest\CreateFilesAndCommitOne;
use Test\States\GitTest\CreateFilesButDoNotCommit;
use Test\States\GitTest\CreateInRemoteAndPullToLocal;
use Test\States\SafeMigrationTest\CommitFilePushAndEditAfter;

class GitTest extends GitBase
{
    public function test_getEditedButNotCommittedFiles(): void
    {
        // Preparing
        $state = new CreateFilesButDoNotCommit($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getEditedButNotCommittedFiles()->parse();

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }

    public function test_getEditedButNotCommittedFiles_when_one_file_has_committed(): void
    {
        // Preparing
        $state = new CreateFilesAndCommitOne($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getEditedButNotCommittedFiles()->parse();

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }

    public function test_getEditedAndCommittedFiles(): void
    {
        // Preparing
        $state = new CreateAndCommitFiles($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getEditedAndCommittedFiles(['origin']);

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);


        // Preparing
        $state->editTwoFiles();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getEditedAndCommittedFiles(['origin']);

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }

    public function test_getEditedAndCommittedFiles_when_given_two_origins(): void
    {
        // Preparing
        $state = new CommitFilesAndAddTwoOrigins($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getEditedAndCommittedFiles(['origin', 'local']);

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);

        $state->removeThirdRemote();
    }

    public function test_getRemoteBranches(): void
    {
        // Preparing
        $state = new AddBranchesPushFilesToThem($this->gitCommand);
        $state->initialize();

        // Action
        $git = new GitQuery($this->repository);
        $actual = $git->getRemoteBranchesByRemotes(['origin', 'local']);

        // Assertion
        $this->assertEqualsCanonicalizing(['master', 'branch2', 'branch3'], $actual);
    }

    public function test_getRemotes(): void
    {
        $this->gitCommand->addRemoteOrigin($this->repository, $this->remoteRepository, 'local');
        $this->gitCommand->addRemoteOrigin($this->repository, $this->remoteRepository, 'test');

        $git = new GitQuery($this->repository);
        $actual = $git->getRemotes()->parse();

        $this->assertEqualsCanonicalizing(['origin', 'local', 'test'], $actual);
    }

    public function test_getLocalBranches(): void
    {
        $this->gitCommand->addBranch('branch2');
        $this->gitCommand->addBranch('branch3');

        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getCurrentBranch()->parse();

        $this->assertEqualsCanonicalizing('branch3', $actual);
    }

    public function test_getCurrentRemoteForBranch(): void
    {
        // Preparing
        $state = new CreateInRemoteAndPullToLocal($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getCurrentBranchRemote()->parse();

        // Assertion
        $this->assertEquals(['origin/master'], $actual);
    }

    public function test_getRemoteCommitHash(): void
    {
        // Preparing
        $state = new CreateInRemoteAndPullToLocal($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getRemoteCommitHash()->parse();

        // Assertion
        $process = new Process(['git', 'rev-parse', 'origin/master'], $this->repository);
        $process->run();
        $expect = trim($process->getOutput());

        $this->assertEquals([$expect], $actual);
    }

    public function test_getPushedFiles(): void
    {
        // Preparing
        $state = new CreateInRemoteAndPullToLocal($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getPulledFiles()->parse();

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }

    public function test_getPushedFiles_when_commit_hash_not_valid(): void
    {
        $this->expectException(NotValidCommitHashException::class);

        // Preparing
        $state = new CreateInRemoteAndPullToLocal($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getPulledFiles('test')->parse();
    }

    public function test_getEditedFilesInActions_method_when_there_are_sensitive_migrations(): void
    {
        // Preparing
        $state = new CommitFilePushAndEditAfter($this->gitCommand);
        $state->initialize();

        $this->gitCommand->add($state->getExpectedFiles()[0]);
        $this->gitCommand->commit();

        $stateActions = new CreateGitHubActionsEnvironment($this->gitCommand);

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getEditedFilesInActions(
            'push',
            $stateActions->getBefore(),
            $stateActions->getAfter()
        )->parse();

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }

    public function test_getEditedFilesInActions_method_when_there_are_no_sensitive_migrations(): void
    {
        // Preparing
        $state = new CreateAndCommitFiles($this->gitCommand);
        $state->initialize();

        $stateActions = new CreateGitHubActionsEnvironment($this->gitCommand);

        // Action
        $gitQuery = new GitQuery($this->repository);
        $actual = $gitQuery->getEditedFilesInActions(
            'push',
            $stateActions->getBefore(),
            $stateActions->getAfter()
        )->parse();

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }
}
