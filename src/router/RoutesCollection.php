<?php

namespace trinity\router;

use trinity\contracts\RoutesCollectionInterface;

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
    /**
     * @param string $route
     * @param string $method
     * @param string|callable $controllerAction
     * @param array $middleware
     * @return void
     */
    private function setRoute(string $route, string $method, string|callable $controllerAction, string $typeResponse, array $middleware = []): void
    {
        if (empty($this->groupPrefixes) === false) {
            $route = $this->getRouteWithGroupPrefix($route);
        }

        $middlewares = [];
        foreach ($this->groupMiddlewares as $item) {
            $middlewares = array_merge($middlewares, $item);
        }

        $middlewares = array_merge($middlewares, $middleware);

        $routeInstance = new Route($this->parseUri($route), $method, $controllerAction, $typeResponse, $middlewares);
        $this->routes[$method][$routeInstance->getUri()['path']] = $routeInstance;
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
     * @param string $typeResponse
     * @param array $middleware
     * @return $this
     */
    public function post(string $route, string|callable $controllerAction, string $typeResponse, array $middleware = []): self
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
    public function get(string $route, string|callable $controllerAction, string $typeResponse, array $middleware = []): self
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
    public function delete(string $route, string|callable $controllerAction, string $typeResponse, array $middleware = []): self
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
    public function put(string $route, string|callable $controllerAction, string $typeResponse, array $middleware = []): self
    {
        $this->setRoute($route, 'PUT', $controllerAction, $typeResponse, $middleware);

        return $this;
    }

    /**
     * @param string $uri
     * @return array
     */
    private function parseUri(string $uri): array
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $params = array_key_exists('query', parse_url($uri)) ? parse_url($uri)['query'] : '';
        $paramArray = explode('{', $params);

        $requiredParams = [];
        $optionalParams = [];

        foreach ($paramArray as $param) {
            if (str_contains($param, '}') === true) {
                $paramName = substr($param, 0, strpos($param, '}'));

                if (str_contains($param, '?') === true) {
                    $optionalParams[] = substr($paramName, 1);
                    continue;
                }

                $requiredParams[] = $paramName;
            }
        }

        return ['path' => $path, 'requiredParams' => $requiredParams, 'optionalParams' => $optionalParams];
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
}
