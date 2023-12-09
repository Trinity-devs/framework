<?php

namespace src\http\errorHandler;

use src\contracts\ResponseInterface;
use src\exception\baseException\ErrorException;
use src\exception\baseException\LogicException;
use src\exception\baseException\UnknownMethodException;
use src\exception\httpException\HttpException;
use src\View;
use Throwable;

class ErrorHandler extends \src\errorHandler\ErrorHandler
{
    public string $traceLine = '{html}';
    private int $maxSourceLines = 19;
    private int $maxTraceSourceLines = 13;

    /**
     * @param View $view
     * @param ResponseInterface $response
     */
    public function __construct(private View $view, private ResponseInterface $response)
    {
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function renderException(Throwable $exception): void
    {
        $response = $this->response->setStatusCodeByException($exception);

        $useErrorView = getenv(
                    'DEBUG'
                ) === 'false' || $exception instanceof HttpException;

        $this->view->clear();

        if (getenv('DEBUG') === 'true') {
            ini_set('display_errors', 1);
        }
            $file = $useErrorView ? 'errorHandler/error' : 'errorHandler/exception';
            $response->withBody($this->renderFile($file, ['exception' => $exception]))->send();
    }

    /**
     * @param string $file
     * @param array $params
     * @return string
     * @throws Throwable
     */
    public function renderFile(string $file, array $params): string
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
    public function renderCallStackItem(
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

    /**
     * @param Throwable $exception
     * @return void
     */
    public static function convertToArray(Throwable $exception): void
    {
        if (getenv('DEBUG') === 'false') {
           new HttpException(500, );
        }
    }
}