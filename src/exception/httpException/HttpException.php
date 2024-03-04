<?php

namespace trinity\exception\httpException;

use trinity\exception\baseException\Exception;
use trinity\http\Response;
use Throwable;

abstract class HttpException extends Exception
{
    private int $statusCode;

    /**
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string|null $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->statusCode = $code;
        parent::__construct((string)$message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (isset(Response::$httpStatuses[$this->statusCode])) {
            return Response::$httpStatuses[$this->statusCode];
        }

        return 'Error';
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
