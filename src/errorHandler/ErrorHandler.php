<?php

namespace src\errorHandler;

use Exception;
use src\contracts\ConsoleKernelInterface;
use src\DIContainer;
use src\exception\baseException\ErrorException;
use Throwable;

abstract class ErrorHandler
{
    private Throwable|null $exception;
    private bool $discardExistingOutput = true;
    private bool $registered = false;
    private string|null $directory;

    public function register(): void
    {
        if ($this->registered === false) {
            $this->setUpErrorHandlers();
            $this->registered = true;
        }
    }

    private function setUpErrorHandlers(): void
    {
        ini_set('display_errors', false);
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);

        if (PHP_SAPI !== 'cli') {
            $this->directory = getcwd();
        }

        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function handleError(int $code, string $message, string $file, int $line): bool
    {
        if (error_reporting() & $code) {
            throw new ErrorException($message, $code, $code, $file, $line);
        }
        return false;
    }

    public function handleException(Throwable $exception): void
    {
        $this->exception = $exception;

        if (PHP_SAPI !== 'cli') {
            http_response_code(500);
        }

        $this->unregister();

        try {
            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
            $this->renderException($exception);

         exit(1);
        } catch (Exception $e) {
            $this->handleFallbackExceptionMessage($e, $exception);
        }

        $this->exception = null;
    }

    private function clearOutput(): void
    {
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (@ob_end_clean() === false) {
                ob_clean();
            }
        }
    }

    abstract protected function renderException(Throwable $exception): void;

    private function handleFallbackExceptionMessage(Throwable $exception, Throwable $previousException): void
    {
        $msg = "Произошла ошибка при обработке другой ошибки:\n";
        $msg .= $exception;
        $msg .= "\nПредыдущее исключение:\n";
        $msg .= $previousException;

        if (getenv('DEBUG') === 'true') {
            if (PHP_SAPI === 'cli') {
                echo $msg . "\n";
            }

            if (PHP_SAPI !== 'cli') {
                echo '<pre>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</pre>';
            }
        }

        if (getenv('DEBUG') === 'false') {
            echo 'Произошла внутренняя ошибка сервера.';
        }

        error_log($msg);

        if (defined('HHVM_VERSION')) {
            flush();
        }
       exit(1);
    }

    public function handleFatalError(): void
    {
        if (isset($this->directory)) {
            chdir($this->directory);
            unset($this->directory);
        }

        $error = error_get_last();

        if ($error === null) {
            return;
        }

        if (ErrorException::isFatalError($error) === false) {
            return;
        }

        if (empty($this->_hhvmException) === false) {
            $this->exception = $this->_hhvmException;
        }

        if (empty($this->_hhvmException) === true) {
            $this->exception = new ErrorException(
                $error['message'],
                $error['type'],
                $error['type'],
                $error['file'],
                $error['line']
            );
        }

        if (isset($this->exception) === false) {
            $this->exception = new ErrorException(
                $error['message'],
                $error['type'],
                $error['type'],
                $error['file'],
                $error['line']
            );
        }
        unset($error);


        if ($this->discardExistingOutput) {
            $this->clearOutput();
        }

        $this->renderException($this->exception);

        if (defined('HHVM_VERSION')) {
            flush();
        }

        register_shutdown_function(function () {
           exit(1);
        });
    }

    private function unregister(): void
    {
        if ($this->registered) {
            $this->directory = null;
            restore_error_handler();
            restore_exception_handler();
            $this->registered = false;
        }
    }
}