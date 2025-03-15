<?php

namespace Aramayismirzoyan\SafeMigrations\Git;

use Aramayismirzoyan\SafeMigrations\Command\Runner;
use Aramayismirzoyan\SafeMigrations\Expressions\GitException;
use Aramayismirzoyan\SafeMigrations\Expressions\NotFoundRemoteException;
use Aramayismirzoyan\SafeMigrations\Expressions\NotValidCommitHashException;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitBranchParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitDiffParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitLsRemoteParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitRemoteParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\GitStatusParser;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\ListParser;
use Aramayismirzoyan\SafeMigrations\SafeMigration;

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
        $command = "git remote";
        $runner = (new Runner($command, $this->repository))->run();
        $output = $runner->getOutput();

        if (!$runner->isSuccessful()) {
            throw new GitException('Git remote command is not successful');
        }

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
        $command = "git ls-remote --heads {$remote}";
        $runner = (new Runner($command, $this->repository))->run();
        $output = $runner->getOutput();

        if (!$runner->isSuccessful()) {
            throw new GitException('Git ls-remote command is not successful');
        }

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
        $command = "git status -s --porcelain";
        $runner = (new Runner($command, $this->repository))->run(true);
        $output = $runner->getOutput();

        if (!$runner->isSuccessful()) {
            throw new GitException('Git status command is not successful');
        }

        return new GitStatusParser($output);
    }

    /**
     * Get current branch
     *
     * @return GitBranchParser
     * @throws GitException
     */
    public function getCurrentBranch(): GitBranchParser
    {
        $command = "git branch";
        $runner = (new Runner($command, $this->repository))->run();
        $output = $runner->getOutput();

        if (!$runner->isSuccessful()) {
            throw new GitException('Git branch command is not successful');
        }

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

        $command = "git diff --stat {$remoteBranch}";
        $runner = (new Runner($command, $this->repository))->run(true);
        $output = $runner->getOutput();

        if (!$runner->isSuccessful()) {
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

        $command = "git cat-file -e {$remoteFull}";
        $runner = (new Runner($command, $this->repository))->run();

        return $runner->isSuccessful();
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
        $command = "git rev-parse --abbrev-ref --symbolic-full-name @{u}";
        $runner = (new Runner($command, $this->repository))->run();
        $output = $runner->getOutput();

        return new ListParser($output);
    }

    /**
     * Get commit pulled by remote branch
     *
     * @return ListParser
     */
    public function getRemoteCommitHash(): ListParser
    {
        $remotes = $this->getCurrentBranchRemote()->parse();
        $remotes = implode(' ', $remotes);

        $command = "git rev-parse " . $remotes;
        $runner = (new Runner($command, $this->repository))->run();
        $output = $runner->getOutput();

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

        $command = "git diff-tree --no-commit-id --name-only -r {$commitHash}";
        $runner = (new Runner($command, $this->repository))->run(true);
        $output = $runner->getOutput();

        if (!$runner->isSuccessful()) {
            throw new NotValidCommitHashException('Not valid commit hash');
        }

        return new ListParser($output);
    }

    /**
     * Get edited files in GitHub Actions during push or pull request
     *
     * @return GitDiffParser
     */
    public function getEditedFilesInActions(): GitDiffParser
    {
        $command = 'git diff --name-only -r HEAD^1 HEAD';

        $runner = (new Runner($command, $this->repository))->run(true);
        $output = $runner->getOutput();

        return new GitDiffParser($output);
    }

    /**
     * Check if the file exists in the main branch
     *
     * @param string $file
     * @param string $branch
     * @param string $token
     * @param string $repository
     * @return bool
     */
    public function hasRemoteFileInActions(string $file, string $branch, string $token, string $repository): bool
    {
        $command = 'curl -s -o /dev/null -w "%{http_code}" ' .
            '-H "Authorization: Bearer ' . $token . '" '.
            '-H "Accept: application/vnd.github.v3+json" '.
            '"https://api.github.com/repos/' . $repository . '/contents/' .
            $file . '?ref=' . $branch . '"';

        $run = (new Runner($command, $this->repository))->run();

        $output = $run->getOutput()[0] ?? null;

        if ($output === '200') {
            return true;
        }

        return false;
    }
}
