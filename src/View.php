<?php

namespace trinity;

use trinity\contracts\{handlers\file\FileHandlerInterface, view\ViewRendererInterface};
use Throwable;
use trinity\exception\baseException\LogicException;

class View implements ViewRendererInterface
{
    public string $title;

    public array $css = [];

    public array $cssFiles = [];

    public array $js = [];

    public array $jsFiles = [];

    public array $linkTags = [];

    public array $metaTags = [];

    /**
     * @param FileHandlerInterface $fileHandler
     */
    public function __construct(
        private FileHandlerInterface $fileHandler,
    ) {
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->metaTags = [];
        $this->linkTags = [];
        $this->css = [];
        $this->cssFiles = [];
        $this->js = [];
        $this->jsFiles = [];
    }

    /**
     * @param string $view
     * @param array $params
     * @return string
     * @throws Throwable
     */
    public function render(string $view, array $params = []): string
    {
        $filePath  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];

        $filename = basename($filePath, '.php');

        $className = lcfirst(str_replace('Controller', '', $filename));

        $viewFile = $this->getViewFile($view, $className);

        if ($viewFile === null || file_exists($viewFile) === false) {
            throw new LogicException("Файл представления не существует: $view");
        }

        return $this->renderPhpFile($viewFile, $params);
    }

    /**
     * @param string $view
     * @param string $controller
     * @return string|null
     */
    private function getViewFile(string $view, string $controller = ''): ?string
    {
        $viewParts = explode('/', $view);

        if ($viewParts[0] === "errorHandler") {
            return $this->fileHandler->getAlias('@viewsTrinity') . "$view" . '.php';
        }

        return $this->fileHandler->getAlias('@views') . "$controller/" . $view . '.php';
    }

    /**
     * @param string $_file
     * @param array $_params
     * @return string
     */
    public function renderPhpFile(string $_file, array $_params = []): string
    {
        $obInitialLevel = ob_get_level();

        ob_start();
        ob_implicit_flush(false);
        extract($_params);

        try {
            require $_file;

            return ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (ob_end_clean() === false) {
                    ob_clean();
                }
            }

            throw $e;
        }
    }
}
