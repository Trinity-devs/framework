<?php

declare(strict_types=1);

namespace trinity\exception\httpException;

use trinity\enum\ExceptionStatusCode;

final class BadRequestHttpException extends HttpException
{
    protected function getStatusCodeEnum(): ExceptionStatusCode
    {
        return ExceptionStatusCode::BadRequest;
    }
}
