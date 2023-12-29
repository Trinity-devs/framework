<?php

namespace trinity\contracts\handlers\error;

use Throwable;

interface ErrorHandlerConsoleInterface
{
    public function register(): void;

    public function handleError(int $code, string $message, string $file, int $line): bool;

    public function handleException(Throwable $exception): void;

    public function handleFatalError(): void;
}