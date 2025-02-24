<?php

namespace Aramayismirzoyan\SafeMigrations;

use Aramayismirzoyan\SafeMigrations\Expressions\NotValidCommitHashException;
use Aramayismirzoyan\SafeMigrations\Git\GitCommand;
use Aramayismirzoyan\SafeMigrations\Git\GitHelper;
use Aramayismirzoyan\SafeMigrations\Git\GitQuery;
use Illuminate\Support\Facades\DB;

final class SafeMigration
{
    public const MIGRATIONS_PATH = 'database/migrations';

    /**
     * @param GitQuery $gitQuery
     * @param GitCommand $gitCommand
     */
    public function __construct(
        private readonly GitQuery   $gitQuery,
        private readonly GitCommand $gitCommand
    ) {
    }

    /**
     * Get changed files for finding sensitive migrations
     *
     * @param array<int, string>|null $remotes
     * @return array<int, string>
     * @throws Expressions\GitException
     */
    private function getChangedFiles(?array $remotes = null): array
    {
        $remotes = $remotes ?? $this->gitQuery->getRemotes()->parse();
        $changed = $this->gitQuery->getEditedButNotCommittedFiles()->parse();

        $committed = $this->gitQuery->getEditedAndCommittedFiles($remotes);

        return array_unique(array_merge($changed, $committed), SORT_REGULAR);
    }

    /**
     * Get remote-branch pairs.
     *
     * @param array<int, string>|null $remotes
     * @param array<int, string>|null $branches
     * @return array<int, string>
     * @throws Expressions\GitException
     * @throws Expressions\NotFoundRemoteException
     */
    private function getRemotesBranches(?array $remotes, ?array $branches): array
    {
        if (is_null($remotes)) {
            $remotes = $this->gitQuery->getRemotes()->parse();
        } else {
            $this->gitQuery->checkRemotes($remotes);
        }

        if (is_null($branches)) {
            $branches = $this->gitQuery->getRemoteBranchesByRemotes($remotes);
        }

        return (new GitHelper())->collectRemotes($remotes, $branches);
    }

    /**
     * Get migrations that create bugs
     *
     * @param array<int, string>|null $files
     * @param array<int, string>|null $remotes
     * @param array<int, string>|null $branches
     * @param bool $fetch
     * @return array<int, string>
     * @throws Expressions\GitException
     * @throws Expressions\NotFoundRemoteException
     */
    public function getSensitiveMigrations(
        ?array $files = null,
        ?array $remotes = null,
        ?array $branches = null,
        bool $fetch = false
    ): array {
        if ($fetch) {
            $this->gitCommand->fetchAll();
        }

        $remotesBranches = $this->getRemotesBranches($remotes, $branches);

        $files = $files ?? $this->getChangedFiles($remotes);

        $migrations = [];

        foreach ($files as $file) {
            foreach ($remotesBranches as $remoteBranch) {
                if ($this->gitQuery->hasRemoteFile($remoteBranch, $file)) {
                    $migrations[] = $file;
                }
            }
        }

        return $migrations;
    }

    /**
     * Get changed migrations that have migrated in the local environment
     *
     * @param string|null $commitHash
     * @return array<int, string>
     * @throws NotValidCommitHashException
     */
    public function getMigratedMigrations(?string $commitHash = null): array
    {
        $pulledFiles = $this->gitQuery->getPulledFiles($commitHash)->parse();
        $pulledFiles = array_map(function ($item) {
            return str_replace([self::MIGRATIONS_PATH . DIRECTORY_SEPARATOR, '.php'], '', $item);
        }, $pulledFiles);

        $migrations = DB::table('migrations')
            ->select(['migration'])
            ->whereIn('migration', $pulledFiles)
            ->pluck('migration');

        return $migrations->toArray();
    }
}
