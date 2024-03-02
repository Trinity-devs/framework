<?php

namespace trinity\exception\httpException;

class ForbiddenException extends HttpException
{
    /**
     * @param $message
     * @param $code
     * @param $previous
     */
    public function __construct($message = null, $code = 403, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}