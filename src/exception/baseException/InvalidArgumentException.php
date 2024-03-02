<?php

namespace trinity\exception\baseException;

class InvalidArgumentException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return ' Invalid argument';
    }
}