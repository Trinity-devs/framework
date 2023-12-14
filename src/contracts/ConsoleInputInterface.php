<?php

namespace trinity\contracts;

interface ConsoleInputInterface
{
    public function getNameCommand(): string|null;

    public function hasArgument(string $name): bool;

    public function getArgument(string $name): int|string;

    public function getOptions(): array;

    public function assignDescriptor(ConsoleCommandInterface $command): void;
}