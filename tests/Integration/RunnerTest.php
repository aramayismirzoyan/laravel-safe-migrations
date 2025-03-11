<?php

namespace Test\Integration;

use Aramayismirzoyan\SafeMigrations\Command\Runner;
use Test\States\GitTest\CreateAndCommitFiles;

class RunnerTest extends GitBase
{
    public function test_run_method(): void
    {
        $state = new CreateAndCommitFiles($this->gitCommand);
        $state->initialize();
        $state->editTwoFiles();
        $files = $state->getExpectedFiles();

        $command = 'git status -s --porcelain';
        $runner = new Runner($command, $this->repository);

        $output = $runner->run()->getOutput();


        $expected = [' M ' . $files[1], ' M ' . $files[2]];

        $this->assertEquals($expected, $output);
        $this->assertTrue($runner->run()->isSuccessful());
    }

    public function test_run_method_when_command_is_not_successful(): void
    {
        $path = dirname(__DIR__, 2);
        $command = 'php tests/command/run';
        $runner = new Runner($command, $path);

        $this->assertFalse($runner->run()->isSuccessful());
    }
}
