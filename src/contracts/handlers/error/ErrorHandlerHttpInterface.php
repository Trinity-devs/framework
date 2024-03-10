<?php

namespace trinity\contracts\handlers\error;

use Throwable;

interface ErrorHandlerHttpInterface
{
    public function register(): void;

    public function handleError(int $code, string $message, string $file, int $line): bool;

    public function handleException(Throwable $exception): void;

    public function handleFatalError(): void;

    public function getExceptionName(): string|null;

    public function htmlEncode(string $text): string;

    public function renderCallStack(): string;

    public function isCoreFile(string $file): bool;

    public function getStatusCode(): int;
}
