<?php

declare(strict_types=1);

namespace trinity\exception\httpException;

final class BadRequestHttpException extends HttpException
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
