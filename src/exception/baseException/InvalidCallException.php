<?php

namespace trinity\exception\baseException;

class InvalidCallException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Invalid Call';
    }
}