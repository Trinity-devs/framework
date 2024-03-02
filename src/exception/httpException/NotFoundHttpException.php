<?php

namespace trinity\exception\httpException;

class NotFoundHttpException extends HttpException
{
    /**
     * @param $message
     * @param $code
     * @param $previous
     */
    public function __construct($message = null, $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}