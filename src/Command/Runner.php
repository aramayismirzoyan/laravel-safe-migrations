<?php

namespace Aramayismirzoyan\SafeMigrations\Command;

use Aramayismirzoyan\SafeMigrations\SafeMigration;

class Runner
{
    /**
     * @param string $command
     * @param string $path
     */
    public function __construct(private readonly string $command, private readonly string $path)
    {
    }

    /**
     * Run command
     *
     * @param bool $migrationsPath
     * @return Output
     */
    public function run(bool $migrationsPath = false): Output
    {
        $command = 'cd ' . $this->path . ' && ';
        $command .= $this->command;


        if (!defined('PHPUNIT_TESTSUITE') && $migrationsPath === true) {
            $command .= ' '.SafeMigration::MIGRATIONS_PATH;
        }
        $command .= ' 2>&1';

        exec($command, $output, $exitCode);

        return new Output($output, $exitCode);
    }
}
