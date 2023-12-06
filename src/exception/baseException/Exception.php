<?php

namespace src\exception\baseException;

class Exception extends \Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Exception';
    }
}