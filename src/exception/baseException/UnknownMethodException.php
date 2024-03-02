<?php

namespace trinity\exception\baseException;

class UnknownMethodException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Unknown method';
    }
}