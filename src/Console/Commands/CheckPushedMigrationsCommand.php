<?php

namespace Aramayismirzoyan\SafeMigrations\Console\Commands;

use Aramayismirzoyan\SafeMigrations\SafeMigration;
use Exception;
use Illuminate\Console\Command;

class CheckPushedMigrationsCommand extends Command
{
    protected $signature = "check:migrations {branches?*} {--remote=*} {--fetch}";

    protected $description = "Check Laravel sensitive migrations";

    /**
     * Output console messages
     *
     * @param array<int, string> $migrations
     * @return void
     */
    private function output(array $migrations): void
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
     * Handle command options and arguments
     *
     * @return array<string, string>
     */
    private function getMethodParameters(): array
    {
        $params = [
            'remotes' => $this->option('remote'),
            'branches' => $this->argument('branches')
        ];

        $methodParameters = [
            'files' => null,
            'fetch' => $this->option('fetch')
        ];

        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $methodParameters[$key] = $value;
            }
        }

        return $methodParameters;
    }

    /**
     * Command handler
     *
     * @param SafeMigration $safeMigration
     * @return void
     */
    public function handle(SafeMigration $safeMigration): void
    {
        $params = $this->getMethodParameters();

        try {
            $migrations = $safeMigration->getSensitiveMigrations(...$params);
            $this->output($migrations);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
