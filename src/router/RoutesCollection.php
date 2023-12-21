<?php

namespace trinity\router;

use trinity\contracts\RoutesCollectionInterface;
use trinity\exception\httpException\NotFoundHttpException;
use trinity\services\UrlParsingService;

class RoutesCollection implements RoutesCollectionInterface
{
    private array $routes = [];
    private array $groupPrefixes = [];
    private array $globalMiddlewares = [];
    private array $groupMiddlewares = [];

    /**
     * @param string $route
     * @param string $method
     * @param string|callable $controllerAction
     * @param array $middleware
     * @return void
     */
    private function setRoute(string $route, string $method, string|callable $controllerAction, array $middleware = []): void
    {
        if (empty($this->groupPrefixes) === false) {
            $route = $this->getRouteWithGroupPrefix($route);
        }

        $middlewares = [];
        foreach ($this->groupMiddlewares as $item) {
            $middlewares = array_merge($middlewares, $item);
        }

        $middlewares = array_merge($middlewares, $middleware);

        $routeInstance = new Route($this->parseUrl($route), $method, $controllerAction, $middlewares);
        $this->routes[$method][$routeInstance->getUrl()['path']] = $routeInstance;
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
     * @param array $middleware
     * @return void
     */
    public function group(string $route, callable $callback, array $middleware = []): void
    {
        $prefix = trim($route, '/');
        $this->groupPrefixes[] = $prefix;
        $this->groupMiddlewares[] = $middleware;
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
     * @param array $middleware
     * @return $this
     */
    public function post(string $route, string|callable $controllerAction, array $middleware = []): self
    {
        $this->setRoute($route, 'POST', $controllerAction, $middleware);

        return $this;
    }

    /**
     * @param string $route
     * @param string|callable $controllerAction
     * @param array $middleware
     * @return $this
     */
    public function get(string $route, string|callable $controllerAction, array $middleware = []): self
    {
        $this->setRoute($route, 'GET', $controllerAction, $middleware);

        return $this;
    }

    /**
     * @param string $route
     * @param string|callable $controllerAction
     * @param array $middleware
     * @return $this
     */
    public function delete(string $route, string|callable $controllerAction, array $middleware = []): self
    {
        $this->setRoute($route, 'DELETE', $controllerAction, $middleware);

        return $this;
    }

    /**
     * @param string $route
     * @param string|callable $controllerAction
     * @param array $middleware
     * @return $this
     */
    public function put(string $route, string|callable $controllerAction, array $middleware = []): self
    {
        $this->setRoute($route, 'PUT', $controllerAction, $middleware);

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
        $path = UrlParsingService::parsePath($url, $params);

        $requiredParams = [];
        $optionalParams = [];

        foreach ($matches as $param) {
            if (str_contains($param, '?') === true) {
                $optionalParams[] = substr($param, 1);

                continue;
            }

            $requiredParams[] = $param;
        }

        return [
            'path' => $path,
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
     * @param array $middleware
     *
     * @return void
     */
    public function addResource(string $route, string $controllerName, array $middleware = []): void
    {
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
            $this->setRoute($item['route'], $item['method'], $item['controllerAction'], $middleware);
        }
    }
}
