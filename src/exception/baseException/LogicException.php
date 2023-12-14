<?php

namespace trinity\exception\baseException;

class LogicException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Logic exception';
    }
}