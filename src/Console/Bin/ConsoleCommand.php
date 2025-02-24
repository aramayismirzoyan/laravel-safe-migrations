<?php

namespace Aramayismirzoyan\SafeMigrations\Console\Bin;

use Aramayismirzoyan\SafeMigrations\Git\GitCommand;
use Aramayismirzoyan\SafeMigrations\Git\GitQuery;
use Aramayismirzoyan\SafeMigrations\SafeMigration;
use Minicli\App;
use Minicli\Command\CommandCall;
use Throwable;

readonly class ConsoleCommand
{
    /**
     * @param App $commandApp
     * @param CommandCall $input
     */
    public function __construct(private App $commandApp, private CommandCall $input)
    {
    }


    /**
     * @param string $name
     * @return array<int, string>|null
     */
    private function getArrayParam(string $name): array|null
    {

        $param = $this->input->getParam($name);

        if ($param === '' || is_null($param)) {
            return null;
        }

        return explode(',', $param);
    }

    /**
     * @param array<int, string> $migrations
     * @return void
     */
    private function output(array $migrations): void
    {
        if (!empty($migrations)) {
            $this->commandApp->error('You have sensitive migrations!');
            foreach ($migrations as $migration) {
                $this->commandApp->info($migration);
            }
        } else {
            $this->commandApp->success("You haven't sensitive migrations.");
        }
    }

    /**
     * @return string
     */
    private function getBasePath(): string
    {
        $basePath = dirname(__DIR__, 5);

        $migrationsPath = $basePath . DIRECTORY_SEPARATOR . SafeMigration::MIGRATIONS_PATH;

        if (!is_dir($migrationsPath)) {
            $basePath = dirname(__DIR__, 6);
        }

        return $basePath;
    }

    /**
     * @return void
     */
    private function checkCommand(): void
    {
        $this->commandApp->registerCommand('check', function () {
            $params = [
                'files' => null,
                'fetch' => $this->input->hasFlag('--fetch'),
                'branches' => $this->getArrayParam('branches'),
                'remotes' => $this->getArrayParam('remotes'),
            ];

            $basePath = $this->getBasePath();

            $gitCommand = new GitCommand($basePath);
            $gitQuery = new GitQuery($basePath);
            $safeMigration = new SafeMigration($gitQuery, $gitCommand);

            try {
                $migrations = $safeMigration->getSensitiveMigrations(...$params);
                $this->output($migrations);
            } catch (Throwable $e) {
                $this->commandApp->error($e->getMessage());
            }
        });
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->checkCommand();

        try {
            $this->commandApp->runCommand($this->input->getRawArgs());
        } catch (Throwable $e) {
            $this->commandApp->error($e->getMessage());
        }
    }
}
