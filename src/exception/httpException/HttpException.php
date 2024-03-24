<?php

declare(strict_types=1);

namespace trinity\exception\httpException;

use Throwable;
use trinity\contracts\exception\ExceptionInterface;
use trinity\enum\ExceptionStatusCode;
use trinity\exception\baseException\Exception;

abstract class HttpException extends Exception implements ExceptionInterface
{
    private int $statusCode;
    private string $statusMessage;

    public function __construct(?string $message = null, int $code = 0, Throwable $previous = null)
    {
        $status = $this->getStatusCodeEnum();
        $this->statusCode = $status->value;
        $this->statusMessage = $message ?? $status->getMessage();

        parent::__construct($this->statusMessage, $code, $previous);
    }

    protected function getStatusCodeEnum(): ExceptionStatusCode
    {
        return ExceptionStatusCode::InternalServerError;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
