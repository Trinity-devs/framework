<?php

declare(strict_types=1);

namespace trinity\console;

enum ConsoleColors: int
{
    case BOLD = 1;
    case BLUE = 34;
    case RED = 31;

    case GREEN = 32;

    case YELLOW = 33;

    case PURPLE = 35;

    case CYAN = 36;
}
