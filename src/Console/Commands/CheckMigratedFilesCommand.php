<?php

namespace Aramayismirzoyan\SafeMigrations\Console\Commands;

use Aramayismirzoyan\SafeMigrations\SafeMigration;
use Exception;
use Illuminate\Console\Command;

class CheckMigratedFilesCommand extends Command
{
    protected $signature = "check:migrated {commitHash?}";

    protected $description = "Check Laravel migrated migrations";

    /**
     * Output console messages
     *
     * @param array<int, string> $migrations
     * @return void
     */
    private function output(array $migrations)
    {
        if (!empty($migrations)) {
            $this->error('You have sensitive migrations!');
            foreach ($migrations as $migration) {
                $this->line($migration);
            }
        } else {
            $this->info("You haven't sensitive migrations.");
        }
    }

    /**
     * Command handler
     *
     * @param SafeMigration $safeMigration
     * @return void
     */
    public function handle(SafeMigration $safeMigration): void
    {
        /**
         * @var string|null $commitHash
         */
        $commitHash = $this->argument('commitHash');

        try {
            $migrations = $safeMigration->getMigratedMigrations($commitHash);
            $this->output($migrations);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
