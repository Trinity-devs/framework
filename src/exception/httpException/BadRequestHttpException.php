<?php

namespace trinity\exception\httpException;

class BadRequestHttpException extends HttpException
{
    /**
     * @param $message
     * @param $code
     * @param $previous
     */
    public function __construct($message = null, $code = 400, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}