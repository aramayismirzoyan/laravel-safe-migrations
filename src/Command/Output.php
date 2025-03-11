<?php

namespace Aramayismirzoyan\SafeMigrations\Command;

readonly class Output
{
    /**
     * @param array<int, string> $output
     * @param int $status
     */
    public function __construct(private array $output, private int $status)
    {
    }

    /**
     * Get output
     *
     * @return array<int, string>
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * Checks if the command is successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->status === 0;
    }
}
