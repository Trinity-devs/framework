<?php

namespace trinity\contracts\handlers\error;

use Throwable;

/**
 * Interface ErrorHandlerHttpInterface defines the contract for HTTP error handling.
 * It includes methods for registering error handlers, handling errors, exceptions,
 * and fatal errors, as well as methods for working with exception data.
 */
interface ErrorHandlerHttpInterface
{
    /**
     * Registers this handler to handle errors, exceptions, and shutdown errors.
     * @return void
     */
    public function register(): void;

    /**
     * Custom error handler function that converts errors into ErrorException.
     * @param int $code The level of the error raised.
     * @param string $message The error message.
     * @param string $file The filename where the error was raised.
     * @param int $line The line number where the error was raised.
     * @return bool Always returns false to indicate the PHP internal error handler should not execute.
     */
    public function handleError(int $code, string $message, string $file, int $line): bool;

    /**
     * Custom exception handler function.
     * @param Throwable $exception The exception that was thrown.
     * @return void
     */
    public function handleException(Throwable $exception): void;

    /**
     * Handles PHP fatal errors by converting them to ErrorException and rendering them.
     * @return void
     */
    public function handleFatalError(): void;

    /**
     * Returns the name of the exception class without the namespace or null if not applicable.
     * @return string|null The simple name of the exception class or null.
     */
    public function getExceptionName(): string|null;

    /**
     * Encodes special characters in a string to HTML entities.
     * @param string $text The string to encode.
     * @return string The encoded string.
     */
    public function htmlEncode(string $text): string;

    /**
     * Renders the call stack of the exception into an HTML string.
     * @return string The HTML representation of the call stack.
     */
    public function renderCallStack(): string;

    /**
     * Checks if a file path is part of the core application files.
     * @param string $file The file path to check.
     * @return bool True if the file is part of the core application, false otherwise.
     */
    public function isCoreFile(string $file): bool;

    /**
     * Returns the HTTP status code appropriate for the exception.
     * @return int The HTTP status code.
     */
    public function getStatusCode(): int;
}
