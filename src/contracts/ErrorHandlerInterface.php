<?php

namespace trinity\contracts;

use Throwable;

interface ErrorHandlerInterface
{
    function register(): void;

    function handleError(int $code, string $message, string $file, int $line): bool;

    function handleHttpException(Throwable $exception): object;

    function handleFatalError(): void;

    function getExceptionName(Throwable $exception): string|null;

    function htmlEncode(string $text): string;

    function renderCallStack(Throwable $exception): string;

    function isCoreFile(string $file): bool;
}