<?php

namespace trinity\http;

use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use Throwable;
use trinity\api\responses\{HtmlResponse, JsonResponse};
use trinity\contracts\handlers\error\ErrorHandlerHttpInterface;
use trinity\contracts\view\ViewRendererInterface;
use trinity\exception\baseException\{ErrorException, LogicException, UnknownMethodException};
use trinity\exception\databaseException\PDOException;
use trinity\exception\httpException\HttpException;

class ErrorHandlerHttp implements ErrorHandlerHttpInterface
{
    const CONTENT_TYPE_JSON = 'application/json';
    private Throwable|null $exception = null;
    private bool $discardExistingOutput = true;
    private bool $isRegistered = false;
    private string|null $directory = null;

    public string $traceLine = '{html}';
    private int $maxSourceLines = 19;
    private int $maxTraceSourceLines = 13;

    /**
     * @param ViewRendererInterface $view
     * @param bool $debug
     * @param string $contentType
     */
    public function __construct(
        private readonly ViewRendererInterface $view,
        private readonly bool                  $debug,
        private readonly string $contentType
    )
    {
        ini_set('display_errors', $this->debug ? '1' : '0');
    }

    /**
     * @return void
     */
    public function register(): void
    {
        if ($this->isRegistered === false) {
            set_exception_handler([$this, 'handleException']);
            set_error_handler([$this, 'handleError']);
            register_shutdown_function([$this, 'handleFatalError']);

            $this->directory = getcwd();
            $this->isRegistered = true;
        }
    }

    /**
     * @param int $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws ErrorException
     */
    public function handleError(int $code, string $message, string $file, int $line): bool
    {
        if (error_reporting() !== 0 && $code) {
            throw new ErrorException($message, $code, $code, $file, $line);
        }

        return false;
    }

    /**
     * @param Throwable $exception
     * @return object
     * @throws Throwable
     */
    public function handleException(Throwable $exception): object
    {
        $this->exception = $exception;
        $this->unregister();

        if ($this->discardExistingOutput) {
            $this->clearOutput();
        }

        return $this->renderException($exception);
    }

    /**
     * @return void
     */
    private function clearOutput(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function handleFatalError(): void
    {
        $error = error_get_last();
        if ($error && ErrorException::isFatalError($error)) {
            $exception = new ErrorException(
                $error['message'],
                $error['type'],
                $error['type'],
                $error['file'],
                $error['line']
            );

            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }

            $this->renderException($exception);
        }
    }

    /**
     * @return void
     */
    private function unregister(): void
    {
        if ($this->isRegistered) {
            restore_error_handler();
            restore_exception_handler();
            $this->isRegistered = false;
        }
    }

    /**
     * @param Throwable $exception
     * @return object
     * @throws Throwable
     */
    private function renderException(Throwable $exception): object
    {
        return $this->contentType === self::CONTENT_TYPE_JSON
            ? new JsonResponse($this->dataJsonException($exception))
            : new HtmlResponse($this->renderHtmlException($exception));
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
     * @return string
     */
    public function getExceptionName(Throwable $exception): string
    {
        if ($exception instanceof HttpException || $exception instanceof UnknownMethodException || $exception instanceof LogicException || $exception instanceof ErrorException || $exception instanceof PDOException) {
            return $exception->getName();
        }

        return $this->getShortNameException($exception);
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
            $file = empty($trace[$i]['file']) === false ? $trace[$i]['file'] : null;
            $line = empty($trace[$i]['line']) === false ? $trace[$i]['line'] : null;
            $class = empty($trace[$i]['class']) === false ? $trace[$i]['class'] : null;
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
        int|null    $line,
        string|null $class,
        string|null $method,
        array       $args,
        int         $index
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
        return str_starts_with(realpath($file), PROJECT_ROOT . DIRECTORY_SEPARATOR);
    }

    /**
     * @param Throwable $exception
     * @return array
     * @throws ReflectionException
     */
    private function dataJsonException(Throwable $exception): array
    {
        $traceItem = $exception->getTrace()[0] ?? null;

        $shortName = 'UnknownClass';

        if ($traceItem && isset($traceItem['class'])) {
            $reflection = new ReflectionClass($traceItem['class']);
            $shortName = $reflection->getShortName();
        }

        $functionName = $traceItem['function'] ?? 'unknownFunction';
        $lineNumber = $exception->getLine();

        $errorString = "$shortName::$functionName on $lineNumber";

        $firstError = $exception->getTrace()[0]['args'][0];

        if ($this->debug === true) {
            return [
                'error' => $errorString,
                'cause' => $exception->getMessage(),
                'type' => $this->getShortNameException($exception),
                'data' => [],
                'trace' => array_map(function ($traceItem) {
                    if (isset($traceItem['args'])) {
                        $traceItem['args'] = $this->serializeTraceArgs($traceItem['args']);
                    }

                    return $traceItem;
                }, $exception->getTrace()),
            ];
        }

        return [
            'cause' => $exception->getMessage(),
            'type' => $this->getShortNameException($exception),
            'data' => [],
        ];
    }

    /**
     * @param Throwable $exception
     * @return int
     */
    public function getStatusCode(Throwable $exception): int
    {
        return $exception->getCode() ? $exception->getCode() : 500;
    }

    /**
     * @param Throwable $exception
     * @return string
     */
    private function getShortNameException(Throwable $exception): string
    {
        $classNameParts = explode('\\', get_class($exception));

        return end($classNameParts);
    }

    /**
     * @param Throwable $exception
     * @return string
     * @throws Throwable
     */
    private function renderHtmlException(Throwable $exception): string
    {
        if ($this->debug) {
            return $this->renderFile('errorHandler/exception', ['exception' => $exception]);
        }

        return $this->renderFile('errorHandler/error', ['exception' => $exception]);
    }

    private function serializeTraceArgs(array $args): array
    {
        return array_map(function ($arg) {
            if (is_object($arg)) {
                $properties = (new ReflectionObject($arg))->getProperties(ReflectionProperty::IS_PUBLIC);
                $propsArray = [];
                foreach ($properties as $property) {
                    $propsArray[$property->getName()] = $this->serializeValue($property->getValue($arg));
                }
                return ['type' => get_class($arg), 'properties' => $propsArray];
            }

            if (is_array($arg)) {
                return ['type' => 'array', 'value' => $this->serializeTraceArgs($arg)];
            }

            return ['type' => gettype($arg), 'value' => $arg];

        }, $args);
    }

    private function serializeValue(mixed $value): string
    {
        if (is_object($value)) {
            return 'Object of class ' . get_class($value);
        }

        if (is_array($value)) {
            return 'Array[' . count($value) . ']';
        }

        return $value;

    }

}