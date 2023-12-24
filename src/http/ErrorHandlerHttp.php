<?php

namespace trinity\http;

use Throwable;
use trinity\api\responses\HtmlResponse;
use trinity\api\responses\JsonResponse;
use trinity\contracts\ErrorHandlerHttpInterface;
use trinity\contracts\ViewRendererInterface;
use trinity\exception\baseException\ErrorException;
use trinity\exception\baseException\Exception;
use trinity\exception\baseException\LogicException;
use trinity\exception\baseException\UnknownMethodException;
use trinity\exception\baseException\ValidationError;
use trinity\exception\httpException\HttpException;

class ErrorHandlerHttp implements ErrorHandlerHttpInterface
{
    private Throwable|null $exception;
    private bool $discardExistingOutput = true;
    private bool $registered = false;
    private string|null $directory;

    public string $traceLine = '{html}';
    private int $maxSourceLines = 19;
    private int $maxTraceSourceLines = 13;
    private string $typeResponse = '';
    private bool $debug = false;

    /**
     * @param ViewRendererInterface $view
     * @param bool $debug
     */
    public function __construct(
        private ViewRendererInterface $view,
        bool $debug,
    ) {
        $this->debug = $debug;

        if ($this->debug === true) {
            ini_set('display_errors', 1);
        }
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
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);

        $this->directory = getcwd();

        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function handleError(int $code, string $message, string $file, int $line): bool
    {
        if (error_reporting() & $code) {
            throw new ErrorException($message, $code, $code, $file, $line);
        }

        return false;
    }

    /**
     * @param Throwable $exception
     * @return object
     * @throws Throwable
     */
    public function handleException(Throwable $exception): mixed
    {
        $this->exception = $exception;

        $this->unregister();

        try {
            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
            $this->exception = null;


            return $this->renderException($exception);
        } catch (Exception $e) {
            return $this->handleFallbackExceptionMessage($e, $exception);
        }
    }

    private function clearOutput(): void
    {
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (@ob_end_clean() === false) {
                ob_clean();
            }
        }
    }

    private function handleFallbackExceptionMessage(
        Throwable $exception,
        Throwable $previousException
    ): JsonResponse|HtmlResponse {
        $msg = 'Произошла ошибка при обработке другой ошибки:<br>';
        $msg .= $exception;
        $msg .= 'Предыдущее исключение:<br>';
        $msg .= $previousException;

        if ($this->debug === true) {
            if ($this->typeResponse === 'json') {
                return new JsonResponse([$msg]);
            }

            return new HtmlResponse('<pre>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</pre>');
        }

        error_log($msg);

        if (defined('HHVM_VERSION')) {
            flush();
        }

        if ($this->typeResponse === 'html') {
            return new JsonResponse(['Произошла внутренняя ошибка сервера.']);
        }

        return new HtmlResponse('Произошла внутренняя ошибка сервера.');
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

    /**
     * @param Throwable $exception
     * @return object
     * @throws Throwable
     */
    private function renderException(Throwable $exception): object
    {
        $useErrorView = $this->debug === false || $exception instanceof HttpException;

        $file = $useErrorView ? 'errorHandler/error' : 'errorHandler/exception';

        if ($this->typeResponse === 'json') {
            return new JsonResponse($this->dataJsonException($exception));
        }

        return new HtmlResponse($this->renderFile($file, ['exception' => $exception]));
    }

    /**
     * @param string $file
     * @param array $params
     * @return string
     * @throws Throwable
     */
    private function renderFile(string $file, array $params): string
    {
        $params['handler'] = $this;

        return $this->view->render($file, $params);
    }

    /**
     * @param Throwable $exception
     * @return string|null
     */
    public function getExceptionName(Throwable $exception): string|null
    {
        if ($exception instanceof HttpException || $exception instanceof UnknownMethodException || $exception instanceof LogicException || $exception instanceof ErrorException) {
            return $exception->getName();
        }

        return null;
    }

    /**
     * @param string $text
     * @return string
     */
    public function htmlEncode(string $text): string
    {
        return htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    /**
     * @param Throwable $exception
     * @return string
     * @throws Throwable
     */
    public function renderCallStack(Throwable $exception): string
    {
        $out = '<ul>';
        $out .= $this->renderCallStackItem($exception->getFile(), $exception->getLine(), null, null, [], 1);
        for ($i = 0, $trace = $exception->getTrace(), $length = count($trace); $i < $length; ++$i) {
            $file = !empty($trace[$i]['file']) ? $trace[$i]['file'] : null;
            $line = !empty($trace[$i]['line']) ? $trace[$i]['line'] : null;
            $class = !empty($trace[$i]['class']) ? $trace[$i]['class'] : null;
            $function = null;

            if (empty($trace[$i]['function']) === false && $trace[$i]['function'] !== 'unknown') {
                $function = $trace[$i]['function'];
            }

            $args = empty($trace[$i]['args']) === false ? $trace[$i]['args'] : [];
            $out .= $this->renderCallStackItem($file, $line, $class, $function, $args, $i + 2);
        }

        $out .= '</ul>';

        return $out;
    }

    /**
     * @param string|null $file
     * @param int|null $line
     * @param string|null $class
     * @param string|null $method
     * @param array $args
     * @param int $index
     * @return string
     * @throws Throwable
     */
    private function renderCallStackItem(
        string|null $file,
        int|null $line,
        string|null $class,
        string|null $method,
        array $args,
        int $index
    ): string {
        if ($file === null || $line === null) {
            return '';
        }

        $line--;
        $lines = file($file);
        if ($lines === false || ($lineCount = count($lines)) < $line || $line < 0) {
            return '';
        }

        $half = (int)(($index === 1 ? $this->maxSourceLines : $this->maxTraceSourceLines) / 2);
        $begin = max($line - $half, 0);
        $end = min($line + $half, $lineCount - 1);

        return $this->renderFile('errorHandler/callStackItem', [
            'file' => $file,
            'line' => $line,
            'class' => $class,
            'method' => $method,
            'index' => $index,
            'lines' => $lines,
            'begin' => $begin,
            'end' => $end,
            'args' => $args,
        ]);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function isCoreFile(string $file): bool
    {
        return $file === null || strpos(realpath($file), PROJECT_ROOT . DIRECTORY_SEPARATOR) === 0;
    }

    private function dataJsonException(Throwable $exception): array
    {
        if ($this->debug === true) {
            return [
                'cause' => $exception->getMessage(),
                'type' => $exception->getName(),
                'data' => [],
            ];
        }

        return [
            'cause' => 'An error occurred',
            'type' => 'Error',
            'data' => [],
        ];
    }

    public function setTypeResponse(string $typeResponse): void
    {
        $this->typeResponse = $typeResponse;
    }

    public function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            $exceptionStatus = $exception->getStatusCode();
        }

        return $exceptionStatus ?? 500;
    }
}