<?php

namespace trinity\exception\baseException;

class InvalidCallException extends \BadMethodCallException
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Invalid Call';
    }
}