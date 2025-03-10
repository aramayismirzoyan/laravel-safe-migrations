<?php

namespace Aramayismirzoyan\SafeMigrations\Console\Commands;

use Aramayismirzoyan\SafeMigrations\Expressions\GitException;
use Aramayismirzoyan\SafeMigrations\Expressions\InvalidMethodArgumentException;
use Aramayismirzoyan\SafeMigrations\Expressions\NotFoundRemoteException;
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
     * @throws GitException
     * @throws InvalidMethodArgumentException
     * @throws NotFoundRemoteException
     */
    public function handle(SafeMigration $safeMigration): void
    {
        $event = getenv('GITHUB_EVENT_NAME');
        $branch = $this->argument('branch');

        $eventFile = file_get_contents(getenv('GITHUB_EVENT_PATH'));
        $eventData = json_decode($eventFile, true);

        $before = $eventData['before'] ?? '';
        $after = $eventData['after'] ?? '';

        if ($safeMigration->hasSensitiveMigrationsInActions($event, $before, $after, $branch)) {
            throw new SensitiveMigrationsException('You have sensitive migrations.');
        }
    }
}
