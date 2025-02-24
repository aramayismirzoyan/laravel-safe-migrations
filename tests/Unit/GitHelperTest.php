<?php

namespace Test\Unit;

use Aramayismirzoyan\SafeMigrations\Git\GitHelper;
use PHPUnit\Framework\TestCase;

class GitHelperTest extends TestCase
{
    public function test_collectOrigins(): void
    {
        $gitHelper = new GitHelper();
        $result = $gitHelper->collectRemotes([
            'origin', 'local', 'test'
        ], [
            'master', 'branch2', 'branch3'
        ]);

        $expects = [
            'origin/master',
            'origin/branch2',
            'origin/branch3',
            'local/master',
            'local/branch2',
            'local/branch3',
            'test/master',
            'test/branch2',
            'test/branch3',
        ];

        $this->assertEquals($expects, $result);
    }
}
