<?php

declare(strict_types=1);

namespace trinity\exception\httpException;

final class ForbiddenException extends HttpException
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
