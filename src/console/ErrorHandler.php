<?php

namespace src\console;

use src\exception\baseException\Exception;
use Throwable;

class ErrorHandler extends \Framework\errorHandler\ErrorHandler
{
    /**
     * @param Throwable $exception
     * @return void
     */
    protected function renderException(Throwable $exception): void
    {
        $previous = $exception->getPrevious();

        if (getenv('DEBUG') === 'false') {
            $this->formatMessage($exception->getName() . ': ') . $exception->getMessage();
        }

        if (getenv('DEBUG') === 'true') {
            $message = $this->formatMessage(
                '<<------------------------------------------------------------' . PHP_EOL,
                [ConsoleColors::RED]
            );
            if ($exception instanceof Exception) {
                $message .= $this->formatMessage("{$exception->getName()}");
            }

            if ($exception instanceof Exception === false) {
                $message .= $this->formatMessage('Exception');
                $this->formatMessage('Error: ') . $exception->getMessage();
            }

            $message .= $this->formatMessage(
                    PHP_EOL . "Class: " . get_class($exception),
                    [ConsoleColors::BOLD, ConsoleColors::BLUE]
                )
                . PHP_EOL . 'With message ' . $this->formatMessage("'{$exception->getMessage()}'", [ConsoleColors::BOLD])
                . "\n\nin " . dirname($exception->getFile()) . DIRECTORY_SEPARATOR . $this->formatMessage(
                    basename($exception->getFile()),
                    [ConsoleColors::BOLD]
                )
                . ':' . $this->formatMessage($exception->getLine(), [ConsoleColors::BOLD, ConsoleColors::YELLOW]
                ) . "\n";

//            if ($exception instanceof \Frameword\exception\db\Exception && empty($exception->errorInfo) === false) {
//                $message .= "\n" . $this->formatMessage("Error Info:\n", [ConsoleColors::BOLD]) . print_r(
//                        $exception->errorInfo,
//                        true
//                    );
//            }

            if ($previous === null) {
                $message .= "\n" . ($this->formatMessage("Stack trace:\n", [ConsoleColors::BOLD]
                    )) . $exception->getTraceAsString();
            }

            $message .= PHP_EOL . PHP_EOL . $this->formatMessage(
                    '------------------------------------------------------------>>',
                    [ConsoleColors::RED]
                );
        }

        if (PHP_SAPI === 'cli') {
            echo fwrite(STDERR, $message . "\n");
        }

        if (PHP_SAPI !== 'cli') {
            echo $message . "\n";
        }

        if (getenv('DEBUG') === 'true' && $previous !== null) {
            $causedBy = $this->formatMessage('Caused by: ', [ConsoleColors::BOLD]);

            if (PHP_SAPI === 'cli') {
                echo fwrite(STDERR, $causedBy);
            }

            if (PHP_SAPI !== 'cli') {
                echo $causedBy;
            }

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
}