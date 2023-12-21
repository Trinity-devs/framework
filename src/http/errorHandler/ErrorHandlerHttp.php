<?php

namespace trinity\http\errorHandler;

use Throwable;
use trinity\apiResponses\HtmlResponse;
use trinity\apiResponses\JsonResponse;
use trinity\contracts\ErrorHandlerInterface;
use trinity\contracts\ResponseInterface;
use trinity\contracts\ViewRendererInterface;
use trinity\exception\baseException\ErrorException;
use trinity\exception\baseException\Exception;
use trinity\exception\baseException\LogicException;
use trinity\exception\baseException\UnknownMethodException;
use trinity\exception\httpException\HttpException;

class ErrorHandlerHttp implements ErrorHandlerInterface
{
    private Throwable|null $exception;
    private bool $discardExistingOutput = true;
    private bool $registered = false;
    private string|null $directory;

    public string $traceLine = '{html}';
    private int $maxSourceLines = 19;
    private int $maxTraceSourceLines = 13;
    private string $typeResponse = '';

    private string $debug;

    /**
     * @param ViewRendererInterface $view
     * @param ResponseInterface $response
     */
    public function __construct(
        private ViewRendererInterface $view,
        private ResponseInterface $response,
        string $debug,
    )
    {
        $this->debug = $debug;
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
        set_exception_handler([$this, 'handleHttpException']);
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

    public function handleHttpException(Throwable $exception): object
    {
        $this->exception = $exception;

        http_response_code(500);

        $this->unregister();

        try {
            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }

            return $this->renderException($exception);
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
        $msg = "Произошла ошибка при обработке другой ошибки:\n";
        $msg .= $exception;
        $msg .= "\nПредыдущее исключение:\n";
        $msg .= $previousException;

        if ($this->debug === 'true') {
            echo '<pre>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</pre>';
        }

        if ($this->debug === 'false') {
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

    /**
     * @param Throwable $exception
     * @return void
     * @throws Throwable
     */
    private function renderException(Throwable $exception): object
    {
        $response = $this->response->setStatusCodeByException($exception);

        $useErrorView = $this->debug === 'false' || $exception instanceof HttpException;

        $this->view->clear();

        if ($this->debug === 'true') {
            ini_set('display_errors', 1);
        }

        $file = $useErrorView ? 'errorHandler/error' : 'errorHandler/exception';

        if ($this->typeResponse === 'html') {
            return new HtmlResponse($this->renderFile($file, ['exception' => $exception]));
        }

        if ($this->typeResponse === 'json') {
            return new JsonResponse($this->dataJsonException($exception));
        }
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

        $this->view->clear();
        return $this->view->render($file, $params);
    }

    /**
     * @param Throwable $exception
     * @return string|null
     */
    public function getExceptionName(Throwable $exception): string|null
    {
        if ($exception instanceof ErrorException) {
            return $exception->getName();
        }

        if ($exception instanceof HttpException || $exception instanceof UnknownMethodException || $exception instanceof LogicException) {
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
        if ($exception instanceof HttpException) {
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'statusCode' => $exception->getStatusCode(),
            ];
        }

        return [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'statusCode' => 500,
        ];
    }

    public function setTypeResponse(string $typeResponse): void
    {
        $this->typeResponse = $typeResponse;
    }
}