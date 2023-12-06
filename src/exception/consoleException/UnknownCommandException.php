<?php

namespace src\exception\consoleException;

use src\exception\baseException\Exception;

class UnknownCommandException extends Exception
{
    /**
     * @param $message
     */
    public function __construct($message = null)
    {
        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Unknown command';
    }
}