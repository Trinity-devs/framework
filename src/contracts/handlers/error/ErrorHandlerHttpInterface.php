<?php

namespace trinity\contracts\handlers\error;

use Throwable;

interface ErrorHandlerHttpInterface
{
    function register(): void;

    function handleError(int $code, string $message, string $file, int $line): bool;

    function handleException(Throwable $exception): mixed;

    function handleFatalError(): void;

    function getExceptionName(Throwable $exception): string|null;

    function htmlEncode(string $text): string;

    function renderCallStack(Throwable $exception): string;

    function isCoreFile(string $file): bool;

    function setTypeResponse(string $typeResponse): void;

    public function getStatusCode(Throwable $exception): int;
}