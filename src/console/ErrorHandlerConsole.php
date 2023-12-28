<?php

namespace trinity\console;

use Throwable;
use trinity\contracts\ErrorHandlerConsoleInterface;
use trinity\exception\baseException\ErrorException;
use trinity\exception\baseException\Exception;

class ErrorHandlerConsole implements ErrorHandlerConsoleInterface
{
    private Throwable|null $exception;
    private bool $discardExistingOutput = true;
    private bool $registered = false;
    private string|null $directory;
    private bool $debug = false;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    private function renderException(Throwable $exception): void
    {
        $previous = $exception->getPrevious();

        $classNameParts = explode('\\', get_class($exception));
        $errorName = end($classNameParts);

        if ($this->debug === false) {
            $this->formatMessage($errorName . ': ') . $exception->getMessage();
        }

        if ($this->debug === true) {
            $message = $this->formatMessage(
                '<<------------------------------------------------------------' . PHP_EOL,
                [ConsoleColors::RED]
            );

            if ($exception instanceof Exception) {
                $message .= $this->formatMessage("{$exception->getName()}");
            }

            if ($exception instanceof Exception === false) {
                $message .= $this->formatMessage($errorName);
                $this->formatMessage("$errorName: ") . $exception->getMessage();
            }

            $message .= $this->formatMessage(PHP_EOL . "Class: " . get_class($exception),[ConsoleColors::BOLD, ConsoleColors::BLUE])
                . PHP_EOL . 'With message ' . $this->formatMessage("'{$exception->getMessage()}'", [ConsoleColors::BOLD])
                . PHP_EOL . PHP_EOL . 'in ' . dirname($exception->getFile()) . DIRECTORY_SEPARATOR . $this->formatMessage(basename($exception->getFile()),[ConsoleColors::BOLD])
                . ':' . $this->formatMessage($exception->getLine(), [ConsoleColors::BOLD, ConsoleColors::YELLOW]
                ) . PHP_EOL;

            if ($previous === null) {
                $message .= PHP_EOL . ($this->formatMessage("Stack trace:\n", [ConsoleColors::BOLD])) . $exception->getTraceAsString();
            }

            $message .= PHP_EOL . PHP_EOL . $this->formatMessage(
                    '------------------------------------------------------------>>',
                    [ConsoleColors::RED]
                );
        }

        echo fwrite(STDERR, $message . PHP_EOL);

        if ($this->debug === true && $previous !== null) {
            $causedBy = $this->formatMessage('Caused by: ', [ConsoleColors::BOLD]);

            echo fwrite(STDERR, $causedBy);

            $this->renderException($previous);
        }
    }

    /**
     * @param string $message
     * @param array $format
     * @return string
     */
    protected function formatMessage(string $message, array $format = [ConsoleColors::RED, ConsoleColors::BOLD]): string
    {
        foreach ($format as $key => $value) {
            $formats[] = $value->value;
        }

        $code = implode(';', $formats);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $message . "\033[0m";
    }

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

    private function handleFallbackExceptionMessage(Throwable $exception, Throwable $previousException): void
    {
        $msg = 'Произошла ошибка при обработке другой ошибки:' . PHP_EOL;
        $msg .= $exception;
        $msg .= PHP_EOL . 'Предыдущее исключение:' . PHP_EOL;
        $msg .= $previousException;

        if ($this->debug === true) {
            echo $msg . PHP_EOL;
        }

        if ($this->debug === false) {
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