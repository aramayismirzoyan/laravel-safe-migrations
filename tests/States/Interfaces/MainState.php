<?php

namespace Test\States\Interfaces;

use Test\Managers\GitManager;

interface MainState
{
    public function __construct(GitManager $gitCommand);
    public function initialize(): void;
}
