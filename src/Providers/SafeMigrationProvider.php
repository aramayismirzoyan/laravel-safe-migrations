<?php

namespace Aramayismirzoyan\SafeMigrations\Providers;

use Aramayismirzoyan\SafeMigrations\Console\Commands\CheckMigratedFilesCommand;
use Aramayismirzoyan\SafeMigrations\Console\Commands\CheckPushedMigrationsCommand;
use Aramayismirzoyan\SafeMigrations\Git\GitCommand;
use Aramayismirzoyan\SafeMigrations\Git\GitQuery;
use Aramayismirzoyan\SafeMigrations\SafeMigration;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class SafeMigrationProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(GitCommand::class, function () {
            return new GitCommand(base_path());
        });

        $this->app->bind(GitQuery::class, function () {
            return new GitQuery(base_path());
        });

        $this->app->bind(SafeMigration::class, function (Application $app) {
            return new SafeMigration(
                $app->make(GitQuery::class),
                $app->make(GitCommand::class),
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands(
                commands: [
                    CheckPushedMigrationsCommand::class,
                    CheckMigratedFilesCommand::class,
                ],
            );
        }
    }
}
