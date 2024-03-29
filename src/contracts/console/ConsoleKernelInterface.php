<?php

namespace trinity\contracts\console;

interface ConsoleKernelInterface
{
    public function handle(): int;

    public function terminate(int $exitStatus): void;

    public function registerCommandNamespaces(array $commandNameSpaces): void;

    public function getCommandMap(): array;
}
