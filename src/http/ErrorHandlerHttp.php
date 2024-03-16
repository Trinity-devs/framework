<?php

declare(strict_types=1);

namespace trinity\http;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use ReflectionException;
use Throwable;
use trinity\contracts\handlers\error\ErrorHandlerHttpInterface;
use trinity\contracts\view\ViewRendererInterface;
use trinity\exception\baseException\{ErrorException, Exception};
use trinity\exception\databaseException\PDOException;
use trinity\helpers\ArrayHelper;

final class ErrorHandlerHttp implements ErrorHandlerHttpInterface
{
    private const CONTENT_TYPE_JSON = 'application/json';
    private const CONTENT_TYPE_HTML = 'text/html';
    private bool $isRegistered = false;
    private int $maxSourceLines = 19;
    private int $maxTraceSourceLines = 13;
    private ?Throwable $exception = null;

    public string $traceLine = '{html}';

    /**
     * @param ViewRendererInterface $viewRenderer
     * @param bool $debug
     * @param string $contentType
     */
    public function __construct(
        private readonly ViewRendererInterface $viewRenderer,
        private readonly bool $debug,
        private string $contentType
    ) {
        $this->contentType = explode(',', $contentType)[0];
        ini_set('display_errors', $this->debug ? '1' : '0');
        ini_set('error_reporting', $this->debug ? E_ALL : E_ERROR | E_WARNING | E_PARSE);
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        if ($this->isRegistered === false) {
            set_exception_handler([$this, 'handleException']);
            set_error_handler([$this, 'handleError']);
            register_shutdown_function([$this, 'handleFatalError']);

            $this->isRegistered = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function handleError(int $code, string $message, string $file, int $line): bool
    {
        if (error_reporting() !== 0 && $code) {
            $this->exception = new ErrorException($message, $code);

            $this->renderException();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function handleException(Throwable $exception): void
    {
        $this->unregister();
        $this->clearOutput();
        $this->exception = $exception;

        $this->renderException();
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
     * @inheritDoc
     */
    public function handleFatalError(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        if (ErrorException::isFatalError($error) === true) {
            $this->exception = new ErrorException(
                $error['message'],
                $error['type'],
                $error['type'],
            );

            $this->clearOutput();

            $this->renderException();
        }
    }

    /**
     * @return void
     */
    private function unregister(): void
    {
        if ($this->isRegistered === true) {
            restore_error_handler();
            restore_exception_handler();
            $this->isRegistered = false;
        }
    }

    /**
     * @throws Throwable
     */
    private function renderException(): void
    {
        $response = new Response(status: $this->getStatusCode(), reason: $this->getExceptionName());
        $response = match ($this->contentType) {
            self::CONTENT_TYPE_JSON => $response
                ->withBody($this->dataJsonException())
                ->withAddedHeader('Content-Type', self::CONTENT_TYPE_JSON),
            self::CONTENT_TYPE_HTML => $response
                ->withBody($this->renderHtmlException())
                ->withHeader('Content-Type', self::CONTENT_TYPE_HTML),
            default => var_dump($this->exception)
        };

        $response->send();
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

        return $this->viewRenderer->render($file, $params);
    }

    /**
     * @inheritDoc
     */
    public function getExceptionName(): string
    {
        if ($this->exception instanceof Exception || $this->exception instanceof PDOException) {
            return $this->exception->getName();
        }

        $classNameParts = explode('\\', get_class($this->exception));

        return end($classNameParts);
    }

    /**
     * @inheritDoc
     */
    public function htmlEncode(string $text): string
    {
        return htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    /**
     * @inheritDoc
     */
    public function renderCallStack(): string
    {
        $out = '<ul>';
        $out .= $this->renderCallStackItem($this->exception->getFile(), $this->exception->getLine(), null, null, [], 1);
        for ($i = 0, $trace = $this->exception->getTrace(), $length = count($trace); $i < $length; ++$i) {
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
     * @inheritDoc
     */
    public function isCoreFile(string $file): bool
    {
        return str_starts_with(realpath($file), PROJECT_ROOT . DIRECTORY_SEPARATOR);
    }

    /**
     * @return StreamInterface
     * @throws ReflectionException
     * @throws Throwable
     */
    private function dataJsonException(): StreamInterface
    {
        $traceItem = ArrayHelper::getValue($this->exception->getTrace(), '0');

        $className = 'UnknownClass';
        $functionName = 'unknownFunction';

        if ($traceItem !== null && isset($traceItem['class'])) {
            $reflection = new ReflectionClass($traceItem['class']);
            $className = $this->replaceDoubleSlash($reflection->getName());
        }

        if ($traceItem !== null && isset($traceItem['function'])) {
            $functionName = $traceItem['function'];
        }

        $lineNumber = $this->exception->getLine();

        $body = [
            'cause' => $this->exception->getMessage(),
            'type' => $this->getExceptionName(),
            'data' => []
        ];

        if ($this->debug === true) {
            $body = [
                'error' => [
                    'file' => $this->exception->getFile(),
                    'function' => $functionName,
                    'class' => $className . ':' . $lineNumber,
                ],
                'cause' => $this->replaceDoubleSlash($this->exception->getMessage()),
                'type' => $this->getExceptionName(),
                'data' => [],
                'trace' => array_map(
                    function ($traceItem) {
                        if (isset($traceItem['class']) === true) {
                            $traceItem['class'] = $this->replaceDoubleSlash($traceItem['class']);
                        }

                        if (isset($traceItem['file']) === true) {
                            $file = $this->replaceWithEmpty("/var/www/html/", $traceItem['file']);
                            $traceItem['file'] = $this->replaceWithEmpty('.php', $file) . ':' . $traceItem['line'];
                        }

                        unset($traceItem['line'], $traceItem['type'], $traceItem['args']);

                        return $traceItem;
                    },
                    $this->exception->getTrace()
                ),
            ];
        }

        $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        return Utils::streamFor($json);
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        if (method_exists($this->exception, 'getStatusCode') === true) {
            return $this->exception->getStatusCode();
        }

        return 500;
    }

    /**
     * @return StreamInterface
     * @throws Throwable
     */
    private function renderHtmlException(): StreamInterface
    {
        if ($this->debug === true) {
            $html = $this->renderFile('errorHandler/exception', ['exception' => $this->exception]);

            return Utils::streamFor($html);
        }

        $html = $this->renderFile('errorHandler/error', ['exception' => $this->exception]);

        return Utils::streamFor($html);
    }

    /**
     * @param string $search Строка поиска
     * @param string $replace Строка замены
     * @param string $subject Строка, в которой производится поиск и замена
     * @return string Возвращает строку с заменёнными значениями
     */
    private function replace(string $search, string $replace, string $subject): string
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     *  Заменяет все вхождения строки поиска на пустую строку
     *
     * @param string $search
     * @param string $subject
     * @return string
     */
    private function replaceWithEmpty(string $search, string $subject): string
    {
        return $this->replace($search, '', $subject);
    }

    /**
     *  Заменяет \\\\ на /
     *
     * @param string $subject
     * @return string
     */
    private function replaceDoubleSlash(string $subject): string
    {
        return $this->replace('\\', '/', $subject);
    }
}
