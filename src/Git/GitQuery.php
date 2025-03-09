<?php

namespace Aramayismirzoyan\SafeMigrations\Git;

use Aramayismirzoyan\SafeMigrations\Expressions\GitException;
use Aramayismirzoyan\SafeMigrations\Expressions\InvalidMethodArgumentException;
use Aramayismirzoyan\SafeMigrations\Expressions\NotFoundRemoteException;
use Aramayismirzoyan\SafeMigrations\Expressions\NotValidCommitHashException;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitBranchParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitDiffParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitLsRemoteParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitRemoteParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitStatusParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\ListParser;
use Aramayismirzoyan\SafeMigrations\SafeMigration;
use Symfony\Component\Process\Process;

class GitQuery
{
    /**
     * @param string $repository
     */
    public function __construct(private readonly string $repository)
    {
    }

    /**
     * Get remote names
     *
     * @return GitRemoteParser
     * @throws GitException
     */
    public function getRemotes(): GitRemoteParser
    {
        $process = new Process(['git', 'remote'], $this->repository);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new GitException('Git remote command is not successful');
        }

        $output = $process->getOutput();

        return new GitRemoteParser($output);
    }

    /**
     * Get remote branches for remote
     *
     * @param string $remote
     * @return GitLsRemoteParser
     * @throws GitException
     */
    public function getRemoteBranchesByRemote(string $remote = 'origin'): GitLsRemoteParser
    {
        $process = new Process(['git', 'ls-remote', '--heads', $remote], $this->repository);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new GitException('Git ls-remote command is not successful');
        }

        $output = $process->getOutput();

        return new GitLsRemoteParser($output);
    }

    /**
     * Get remote branches for multiple remotes
     *
     * @param array<int, string> $remotes
     * @return array<int, string>
     * @throws GitException
     */
    public function getRemoteBranchesByRemotes(array $remotes): array
    {
        $resultAll = [];

        foreach ($remotes as $remote) {
            $output = $this->getRemoteBranchesByRemote($remote)->parse();

            if (empty($output)) {
                continue;
            }

            $resultAll = array_merge($resultAll, $output);
        }

        return array_unique($resultAll, SORT_REGULAR);
    }

    /**
     * Get edited but not committed files.
     * Get newly created files but not added files.
     * Get added files.
     * Get committed and then edit files.
     * Get added and then edited files
     *
     * @return GitStatusParser
     * @throws GitException
     */
    public function getEditedButNotCommittedFiles(): GitStatusParser
    {
        $command = ['git', 'status', '-s', '--porcelain'];

        if (!defined('PHPUNIT_TESTSUITE')) {
            $command[] = SafeMigration::MIGRATIONS_PATH;
        }

        $process = new Process($command, $this->repository);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new GitException('Git status command is not successful');
        }

        $result = $process->getOutput();

        return new GitStatusParser($result);
    }

    /**
     * Get current branch
     *
     * @return GitBranchParser
     * @throws GitException
     */
    public function getCurrentBranch(): GitBranchParser
    {
        $process = new Process(['git',  'branch'], $this->repository);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new GitException('Git branch command is not successful');
        }

        $output = $process->getOutput();

        return new GitBranchParser($output);
    }

    /**
     * Get added or committed files for remote
     *
     * @param string $remote
     * @return GitDiffParser
     * @throws GitException
     */
    public function getEditedAndCommittedFilesForRemote(string $remote): GitDiffParser
    {
        $currentBranch = $this->getCurrentBranch()->parse();

        $remoteBranch = $remote . '/' . $currentBranch;

        $command = 'cd ' . $this->repository . ' && ';
        $command .= "git diff --stat {$remoteBranch}";

        if (!defined('PHPUNIT_TESTSUITE')) {
            $command .= ' ' . SafeMigration::MIGRATIONS_PATH;
        }

        $command .= ' 2>&1';

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new GitException('Git diff command is not successful');
        }

        return new GitDiffParser($output);
    }

    /**
     * Get added or committed files for multiple remotes
     *
     * @param array<int, string> $remotes
     * @return array<int, string>
     * @throws GitException
     */
    public function getEditedAndCommittedFiles(array $remotes): array
    {
        $resultAll = [];

        foreach ($remotes as $remote) {
            $output = $this->getEditedAndCommittedFilesForRemote($remote)->parse();

            if (empty($output)) {
                continue;
            }

            $resultAll = array_merge($resultAll, $output);
        }

        return $resultAll;
    }

    /**
     * Check existing file in the remote repository
     *
     * @param string $remoteBranch
     * @param string $file
     * @return bool
     */
    public function hasRemoteFile(string $remoteBranch, string $file): bool
    {
        if (!defined('PHPUNIT_TESTSUITE')) {
            $file = SafeMigration::MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $file;
        }

        $remoteFull = "{$remoteBranch}:{$file}";
        $command = ['git', 'cat-file', '-e', $remoteFull];

        $process = new Process($command, $this->repository);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Check existing remotes
     *
     * @param array<int, string> $inputRemotes
     * @return void
     * @throws GitException
     * @throws NotFoundRemoteException
     */
    public function checkRemotes(array $inputRemotes): void
    {
        $remotes = $this->getRemotes()->parse();

        foreach ($inputRemotes as $inputRemote) {
            if (!in_array($inputRemote, $remotes)) {
                throw new NotFoundRemoteException("There is no '$inputRemote' remote");
            }
        }
    }

    /**
     * Get the current branch remote
     *
     * @return ListParser
     */
    public function getCurrentBranchRemote(): ListParser
    {
        $command = [
            'git', 'rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}'
        ];

        $process = new Process($command, $this->repository);
        $process->run();

        $output = $process->getOutput();

        return new ListParser($output);
    }

    /**
     * Get commit pulled by remote branch
     *
     * @return ListParser
     */
    public function getRemoteCommitHash(): ListParser
    {
        $remote = $this->getCurrentBranchRemote()->parse();

        $process = new Process(['git', 'rev-parse', ...$remote], $this->repository);
        $process->run();

        $output = $process->getOutput();

        return new ListParser($output);
    }

    /**
     * Get changed files during the pull
     *
     * @param string|null $commitHash
     * @return ListParser
     * @throws NotValidCommitHashException
     */
    public function getPulledFiles(?string $commitHash = null): ListParser
    {
        if (is_null($commitHash)) {
            $commitHash = $this->getRemoteCommitHash()->parse()[0];
        }

        $command = [
            'git',
            'diff-tree',
            '--no-commit-id',
            '--name-only',
            '-r',
            $commitHash
        ];

        if (!defined('PHPUNIT_TESTSUITE')) {
            $command[] = SafeMigration::MIGRATIONS_PATH;
        }

        $process = new Process($command, $this->repository);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new NotValidCommitHashException('Not valid commit hash');
        }

        $output = $process->getOutput();

        return new ListParser($output);
    }

    /**
     * Get edited files in GitHub Actions during push or pull request
     *
     * @param string $event
     * @param string|null $before
     * @param string|null $after
     * @return GitDiffParser
     * @throws InvalidMethodArgumentException
     */
    public function getEditedFilesInActions(string $event, ?string $before = null, ?string $after = null): GitDiffParser
    {
        $command = 'cd '.$this->repository.' && ';
        $command .= 'git diff --name-only ';

        if ($event == 'pull_request') {
            $command .= '-r HEAD^1 HEAD';
        } else {
            if (is_null($after) || is_null($before)) {
                throw new InvalidMethodArgumentException('You need to pass $after and $before for the push event');
            }

            $command .= $before.' '.$after;
        }

        if (! defined('PHPUNIT_TESTSUITE')) {
            $command .= ' '.SafeMigration::MIGRATIONS_PATH;
        }

        $command .= ' 2>&1';

        exec($command, $output, $exitCode);

        return new GitDiffParser($output);
    }
}
