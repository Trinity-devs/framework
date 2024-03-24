<?php

declare(strict_types=1);

namespace trinity\exception\httpException;

use trinity\enum\ExceptionStatusCode;

final class NotFoundHttpException extends HttpException
{
    protected function getStatusCodeEnum(): ExceptionStatusCode
    {
        return ExceptionStatusCode::NotFound;
    }
}
