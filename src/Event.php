<?php

namespace src;

enum Event: string
{
    case CONSOLE_INPUT_READY = 'console_input_ready';

    case CONSOLE_COMMAND_STARTED = 'console_command_started';

    case CONSOLE_COMMAND_DONE = 'console_command_done';
}
