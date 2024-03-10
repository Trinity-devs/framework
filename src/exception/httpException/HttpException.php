<?php

declare(strict_types=1);

namespace trinity\exception\httpException;

use Throwable;
use trinity\contracts\exception\ExceptionInterface;
use trinity\exception\baseException\Exception;
use trinity\http\Response;

abstract class HttpException extends Exception implements ExceptionInterface
{
    private int $statusCode;

    /**
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string|null $message = null, int $code = 500, Throwable $previous = null)
    {
        $this->statusCode = $code;
        parent::__construct((string)$message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return Response::$httpStatuses[$this->statusCode];
    }
}
