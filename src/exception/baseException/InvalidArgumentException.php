<?php

namespace src\exception\baseException;

use BadMethodCallException;

class InvalidArgumentException extends BadMethodCallException
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return ' Invalid argument';
    }
}