<?php

namespace src\exception\httpException;

class ForbiddenException extends HttpException
{
    /**
     * @param $message
     * @param $code
     * @param $previous
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}