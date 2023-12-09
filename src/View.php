<?php

namespace trinity;

use trinity\contracts\RouterInterface;
use trinity\contracts\ViewRendererInterface;
use trinity\exception\baseException\LogicException;

const VIEW_PATH = __DIR__ . '/views/';

class View implements ViewRendererInterface
{
    public string $title;

    public array $css = [];

    public array $cssFiles = [];

    public array $js = [];

    public array $jsFiles = [];

    public array $linkTags = [];

    public array $metaTags = [];

    public function __construct(
        private RouterInterface $router,
    )
    {
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
     * @throws \Throwable
     */
    public function render(string $view, array $params = []): string
    {
       $controller = $this->router->getControllerName();

        preg_match('/\\\\([a-zA-Z]+)Controller$/', $controller, $matches);

        $viewFile = $this->getViewFile($view, lcfirst($matches[1]));

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
            return VIEW_PATH . $view . '.php';
        }

        return PROJECT_ROOT . "views/$controller/" . $view . '.php';
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
        } catch (\Throwable $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (ob_end_clean() === false) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }
}
