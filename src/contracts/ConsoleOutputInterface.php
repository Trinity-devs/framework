<?php

namespace src\contracts;

use src\console\ConsoleColors;

interface ConsoleOutputInterface
{
    public function writeLn(int $carriagesLength = 1): void;

    public function ansiFormat(string $message, ConsoleColors $color): void;

    public function stdout(string $message): void;
}