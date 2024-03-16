<?php

declare(strict_types=1);

namespace trinity\enum;

enum ExceptionStatusCode: int
{
    case BadRequest = 400;
    case Forbidden = 403;
    case NotAcceptable = 406;
    case NotFound = 404;
    case Unauthorized = 401;
    case InternalServerError = 500;

    public function getMessage(): string
    {
        return match ($this) {
            self::BadRequest => 'Bad Request',
            self::Forbidden => 'Forbidden',
            self::NotAcceptable => 'Not Acceptable',
            self::NotFound => 'Not Found',
            self::Unauthorized => 'Unauthorized',
            default => 'Internal Server Error',
        };
    }
}
