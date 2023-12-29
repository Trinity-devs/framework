<?php

namespace trinity\router;

use trinity\contracts\router\RoutesCollectionInterface;
use trinity\exception\httpException\NotFoundHttpException;
use trinity\services\UrlParsingService;

class RoutesCollection implements RoutesCollectionInterface
{
    private array $routes = [];
    private array $groupPrefixes = [];
    private array $globalMiddlewares = [];
    private array $groupMiddlewares = [];
    private string $groupTypeResponse = '';

    public const TYPE_RESPONSE_JSON = 'json';
    public const TYPE_RESPONSE_HTML = 'html';

    /**
     * @param string $route
     * @param string $method
     * @param string|callable $controllerAction
     * @param string $typeResponse
     * @param array $middleware
     * @return void
     */
    private function setRoute(string $route, string $method, string|callable $controllerAction, string $typeResponse = self::TYPE_RESPONSE_HTML, array $middleware = []): void
    {
        if (empty($this->groupPrefixes) === false) {
            $route = $this->getRouteWithGroupPrefix($route);
        }

        $middlewares = [];
        foreach ($this->groupMiddlewares as $item) {
            $middlewares = array_merge($middlewares, $item);
        }

        $middlewares = array_merge($middlewares, $middleware);

        if ($this->groupTypeResponse !== '') {
            $typeResponse = $this->groupTypeResponse;
        }

        $routeInstance = new Route($this->parseUrl($route), $method, $controllerAction, $typeResponse, $middlewares);
        $this->routes[][$routeInstance->getUrl()['quoteUrl']] = $routeInstance;
    }

    /**
     * @param string $uri
     * @return string
     */
    private function getRouteWithGroupPrefix(string $uri): string
    {
        $uri = trim($uri, '/');
        $prefix = '/' . implode('/', $this->groupPrefixes);

        return rtrim($prefix . '/' . $uri, '/');
    }

    /**
     * @param string $uri
     * @return string
     */
    private function getRouteUri(string $uri): string
    {
        $uri = trim($uri, '/');
        $prefix = '/' . implode('/', $this->groupPrefixes);

        return rtrim($prefix . '/' . $uri, '/');
    }

    /**
     * @param string $route
     * @param callable $callback
     * @param string $typeResponse
     * @param array $middleware
     * @return void
     */
    public function group(string $route, callable $callback, string $typeResponse = '', array $middleware = []): void
    {
        $prefix = trim($route, '/');
        $this->groupPrefixes[] = $prefix;
        $this->groupMiddlewares[] = $middleware;

        if ($typeResponse !== '') {
            $this->groupTypeResponse = $typeResponse;
        }

        $callback($this);

        array_pop($this->groupMiddlewares);
        array_pop($this->groupPrefixes);
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param string $route
     * @param string|callable $controllerAction
     * @param string $typeResponse
     * @param array $middleware
     * @return $this
     */
    public function post(string $route, string|callable $controllerAction, string $typeResponse = self::TYPE_RESPONSE_HTML, array $middleware = []): self
    {
        $this->setRoute($route, 'POST', $controllerAction, $typeResponse, $middleware);

        return $this;
    }

    /**
     * @param string $route
     * @param string|callable $controllerAction
     * @param string $typeResponse
     * @param array $middleware
     * @return $this
     */
    public function get(string $route, string|callable $controllerAction, string $typeResponse = self::TYPE_RESPONSE_HTML, array $middleware = []): self
    {
        $this->setRoute($route, 'GET', $controllerAction, $typeResponse, $middleware);

        return $this;
    }

    /**
     * @param string $route
     * @param string|callable $controllerAction
     * @param string $typeResponse
     * @param array $middleware
     * @return $this
     */
    public function delete(string $route, string|callable $controllerAction, string $typeResponse = self::TYPE_RESPONSE_HTML, array $middleware = []): self
    {
        $this->setRoute($route, 'DELETE', $controllerAction, $typeResponse, $middleware);

        return $this;
    }

    /**
     * @param string $route
     * @param string|callable $controllerAction
     * @param string $typeResponse
     * @param array $middleware
     * @return $this
     */
    public function put(string $route, string|callable $controllerAction, string $typeResponse = self::TYPE_RESPONSE_HTML, array $middleware = []): self
    {
        $this->setRoute($route, 'PUT', $controllerAction, $typeResponse, $middleware);

        return $this;
    }

    /**
     * @param string $url
     * @return array
     * @throws NotFoundHttpException
     */
    private function parseUrl(string $url): array
    {
        $params = UrlParsingService::parseParams($url);
        $matches = UrlParsingService::parseQuery($url);
        $path = UrlParsingService::parsePath($url, $matches[0]);

        $matchesUrl = $url;
        foreach ($matches[0] as $match) {
            $matchesUrl = str_replace($match, '(\d+)', $matchesUrl);
        }

        $quoteUrl = '/' . preg_quote($matchesUrl, '/') . '/';
        $quoteUrl = str_replace('\(\\\d\+\)', '(\d+)', $quoteUrl);

        $requiredParams = [];
        $optionalParams = [];

        foreach ($matches[1] as $param) {
            if (str_contains($param, '?') === true) {
                $optionalParams[] = substr($param, 1);

                continue;
            }

            $requiredParams[] = $param;
        }

        return [
//            'path' => $path,
            'quoteUrl' => $quoteUrl,
            'params' => $params,
            'requiredParams' => $requiredParams,
            'optionalParams' => $optionalParams
        ];
    }

    /**
     * @param array|string|null $middleware
     * @return self|array
     */
    public function middleware(array|string|null $middleware = null): self|array
    {
        if (is_null($middleware)) {
            return (array)($this->globalMiddlewares ?? []);
        }

        if (is_array($middleware) === false) {
            $middleware = func_get_args();
        }

        foreach ($middleware as $index => $value) {
            $middleware[$index] = (string)$value;
        }

        $this->globalMiddlewares = array_merge(
            (array)($this->globalMiddlewares ?? []), $middleware
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getGlobalMiddlewares(): array
    {
        return $this->globalMiddlewares;
    }

    /**
     * @param string $route
     * @param string $controllerName
     * @param string $typeResponse
     * @param array $middleware
     *
     * @return void
     */
    public function addResource(string $route, string $controllerName, string $typeResponse = '', array $middleware = []): void
    {
        if ($this->groupTypeResponse !== '') {
            $typeResponse = $this->groupTypeResponse;
        }

        $routesMap = [
            [
                'method' => 'GET',
                'route' => $route,
                'controllerAction' => $controllerName . '::actionList',
            ],
            [
                'method' => 'GET',
                'route' => $route . '/{id}',
                'controllerAction' => $controllerName . '::actionListItem',
            ],
            [
                'method' => 'POST',
                'route' => $route,
                'controllerAction' => $controllerName . '::actionCreate',
            ],
            [
                'method' => 'PUT',
                'route' => $route . '/{id}',
                'controllerAction' => $controllerName . '::actionUpdate',
            ],
            [
                'method' => 'PATCH',
                'route' => $route . '/{id}',
                'controllerAction' => $controllerName . '::actionPatch',
            ],
            [
                'method' => 'DELETE',
                'route' => $route . '/{id}',
                'controllerAction' => $controllerName . '::actionDelete',
            ],
        ];

        foreach ($routesMap as $item) {
            $this->setRoute($item['route'], $item['method'], $item['controllerAction'], $typeResponse, $middleware);
        }
    }
}
