<?php

declare(strict_types=1);

namespace trinity\console;

use trinity\contracts\console\ConsoleOutputInterface;

final class ConsoleOutput implements ConsoleOutputInterface
{
    /**
     * @param int $carriagesLength
     * @return void
     */
    public function writeLn(int $carriagesLength = 1): void
    {
        echo str_repeat(PHP_EOL, $carriagesLength);
    }

    /**
     * @param string $message
     * @param ConsoleColors $color
     * @return void
     */
    public function ansiFormat(string $message, ConsoleColors $color): void
    {
        echo "\033[{$color->value}m{$message}\033[0m";
    }

    /**
     * @param string $message
     * @return void
     */
    public function stdout(string $message): void
    {
        echo $message;
    }
}
