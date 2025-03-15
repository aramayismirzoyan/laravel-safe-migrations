<?php

namespace Aramayismirzoyan\SafeMigrations\Console\Commands;

use Aramayismirzoyan\SafeMigrations\Expressions\SensitiveMigrationsException;
use Aramayismirzoyan\SafeMigrations\SafeMigration;
use Illuminate\Console\Command;

class GitHubActionsCommand extends Command
{
    protected $signature = 'github:actions {branch}';

    protected $description = 'Check Laravel migrated migrations';

    /**
     * @param SafeMigration $safeMigration
     * @return void
     * @throws SensitiveMigrationsException
     */
    public function handle(SafeMigration $safeMigration): void
    {
        $event = getenv('GITHUB_EVENT_NAME');
        $branch = $this->argument('branch');
        $token = getenv('GITHUB_TOKEN');
        $repository = getenv('GITHUB_REPOSITORY');
        $ref = getenv('GITHUB_REF');
        $currentBranch = str_replace('refs/heads/', '', $ref);

        if ($event == 'pull_request' && $currentBranch !== $branch) {
            $safeMigration->checkSensitiveMigrationsInAction($branch, $token, $repository);
        }
    }
}
