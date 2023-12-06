<?php

namespace src\exception\baseException;

class UnknownMethodException extends \BadMethodCallException
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Unknown method';
    }
}