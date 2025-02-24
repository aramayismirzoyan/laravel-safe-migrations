<?php

namespace Test\Integration;

use Aramayismirzoyan\SafeMigrations\Expressions\NotFoundRemoteException;
use Aramayismirzoyan\SafeMigrations\Git\GitCommand;
use Aramayismirzoyan\SafeMigrations\Git\GitQuery;
use Aramayismirzoyan\SafeMigrations\SafeMigration;
use Test\States\SafeMigrationTest\CommitFilePushAndEditAfter;
use Test\States\SafeMigrationTest\CreateBranchPushEditSameInLocal;
use Test\States\SafeMigrationTest\CreateFourFilesAndPushTwo;
use Test\States\SafeMigrationTest\PushCommitAndDoNotCommitFiles;
use Test\States\SafeMigrationTest\PushToRemoteBranchEditSameInLocal;

class SafeMigrationTest extends GitBase
{
    public function test_getSensitiveMigrations_when_there_are_committed_and_never_committed_files(): void
    {
        // Preparing
        $state = new CreateFourFilesAndPushTwo($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $gitCommand = new GitCommand($this->repository);

        $migrations = new SafeMigration($gitQuery, $gitCommand);
        $changedFiles = $gitQuery->getEditedButNotCommittedFiles()->parse();
        $actual = $migrations->getSensitiveMigrations($changedFiles);

        // Assertion
        $this->assertEquals([], $actual);
    }

    public function test_getSensitiveMigrations_if_file_committed_pushed_and_after_edited(): void
    {
        // Preparing
        $state = new CommitFilePushAndEditAfter($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $gitCommand = new GitCommand($this->repository);

        $migrations = new SafeMigration($gitQuery, $gitCommand);
        $changedFiles = $gitQuery->getEditedButNotCommittedFiles()->parse();
        $actual = $migrations->getSensitiveMigrations($changedFiles);

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }

    public function test_getSensitiveMigrations_when_there_are_the_committed_and_then_edited_files(): void
    {
        // Preparing
        $state = new PushToRemoteBranchEditSameInLocal($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $gitCommand = new GitCommand($this->repository);

        $migrations = new SafeMigration($gitQuery, $gitCommand);
        $committedFiles = $gitQuery->getEditedAndCommittedFiles(['origin']);
        $actual = $migrations->getSensitiveMigrations($committedFiles);

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }

    public function test_getSensitiveMigrations_when_multiple_branches(): void
    {
        // Preparing
        $state = new CreateBranchPushEditSameInLocal($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $gitCommand = new GitCommand($this->repository);

        $migrations = new SafeMigration($gitQuery, $gitCommand);
        $actual = $migrations->getSensitiveMigrations(fetch: true);

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $actual);
    }

    public function test_getSensitiveMigrations_fetch_param_is_false(): void
    {
        // Preparing
        $state = new CreateBranchPushEditSameInLocal($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $gitCommand = new GitCommand($this->repository);

        $migrations = new SafeMigration($gitQuery, $gitCommand);
        $migrations = $migrations->getSensitiveMigrations(fetch: false);

        // Assertion
        $this->assertEquals([], $migrations);
    }

    public function test_getSensitiveMigrations(): void
    {
        // Preparing
        $state = new PushCommitAndDoNotCommitFiles($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $gitCommand = new GitCommand($this->repository);

        $git = new SafeMigration($gitQuery, $gitCommand);
        $migrations = $git->getSensitiveMigrations();

        // Assertion
        $this->assertEquals($state->getExpectedFiles(), $migrations);
    }

    public function test_getSensitiveMigrations_trow_exception_when_given_not_valid_remote(): void
    {
        $this->expectException(NotFoundRemoteException::class);

        // Preparing
        $state = new PushCommitAndDoNotCommitFiles($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $gitCommand = new GitCommand($this->repository);

        $git = new SafeMigration($gitQuery, $gitCommand);
        $migrations = $git->getSensitiveMigrations(remotes: ['user1']);
    }

    public function test_getSensitiveMigrations_given_not_valid_branch(): void
    {
        // Preparing
        $state = new PushCommitAndDoNotCommitFiles($this->gitCommand);
        $state->initialize();

        // Action
        $gitQuery = new GitQuery($this->repository);
        $gitCommand = new GitCommand($this->repository);

        $git = new SafeMigration($gitQuery, $gitCommand);
        $migrations = $git->getSensitiveMigrations(branches: ['user-branch']);

        // Assertion
        $this->assertEquals([], $migrations);
    }
}
