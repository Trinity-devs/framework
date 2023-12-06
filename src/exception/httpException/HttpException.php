<?php

namespace src\exception\httpException;

use src\exception\baseException\Exception;
use src\Response;
use Throwable;

class HttpException extends Exception
{
public int $statusCode;

    /**
     * @param int $status
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $status, string|null $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->statusCode = $status;
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
}