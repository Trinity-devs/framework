<?php

namespace trinity\exception\httpException;

class NotAcceptableHttpException extends HttpException
{
    /**
     * @param $message
     * @param $code
     * @param $previous
     */
    public function __construct($message = null, $code = 406, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
