<?php

namespace Test\Feature;

use Aramayismirzoyan\SafeMigrations\Git\GitCommand;
use Aramayismirzoyan\SafeMigrations\Git\GitQuery;
use Aramayismirzoyan\SafeMigrations\Git\Parsers\ListParser;
use Aramayismirzoyan\SafeMigrations\SafeMigration;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

class SafeMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(dirname(__DIR__, 2) . '/workbench/database/migrations');
        $this->artisan('migrate', ['--database' => 'testing']);
    }

    public function test_getMigratedMigrations(): void
    {
        // Preparing
        $migrations = DB::table('migrations')
            ->select()->pluck('migration')->toArray();

        $listParserCommandMock = $this->createMock(ListParser::class);
        $listParserCommandMock->method('parse')->willReturn([
            SafeMigration::MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $migrations[0] . '.php',
            SafeMigration::MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $migrations[1] . '.php',
        ]);

        $gitQueryMock = $this->createMock(GitQuery::class);
        $gitQueryMock->method('getPulledFiles')->willReturn($listParserCommandMock);

        $gitCommandMock = $this->createMock(GitCommand::class);

        // Action
        $safeMigrations = new SafeMigration($gitQueryMock, $gitCommandMock);
        $actual = $safeMigrations->getMigratedMigrations();

        // Assertion
        $this->assertEquals([$migrations[0], $migrations[1]], $actual);
    }
}
