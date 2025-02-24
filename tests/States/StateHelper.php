<?php

namespace Test\States;

trait StateHelper
{
    protected function createAndAdd(string $file, ?string $path = null): void
    {
        $this->gitCommand->getFileManager()->create($file, $path);
        $this->gitCommand->add($file, $path);
    }
}
