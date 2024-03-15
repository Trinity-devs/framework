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
    public function __construct($message = null, $code, $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}
