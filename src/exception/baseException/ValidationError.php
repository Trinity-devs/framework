<?php

namespace trinity\exception\baseException;

class ValidationError extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'ValidationError';
    }

    /**
     * @param $message
     */
    public function __construct($message = null)
    {
        parent::__construct($message);
    }
}
