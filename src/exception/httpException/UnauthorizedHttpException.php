<?php

namespace src\exception\httpException;

class UnauthorizedHttpException extends HttpException
{
    /**
     * @param $message
     * @param $code
     * @param $previous
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}