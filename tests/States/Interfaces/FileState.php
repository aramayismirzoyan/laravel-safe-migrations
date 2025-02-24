<?php

namespace Test\States\Interfaces;

interface FileState extends MainState
{
    /**
     * @return array<int, string>
     */
    public function getExpectedFiles(): array;
}
