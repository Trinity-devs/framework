<?php

declare(strict_types=1);

namespace trinity\exception\consoleException;

use trinity\exception\baseException\Exception;

final class UnknownCommandException extends Exception
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
