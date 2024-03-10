<?php

namespace trinity\contracts\console;

interface ConsoleCommandInterface
{
    public static function getSignature(): string;

    public static function getDescription(): string;

    public static function getHidden(): bool;

    public function execute(): void;
}
