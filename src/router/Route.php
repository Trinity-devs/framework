<?php

namespace src\router;

class Route
{
    /**
     * @param array $uri
     * @param string $method
     * @param mixed $controllerAction
     * @param array $middlewares
     */
    public function __construct(
        private array  $uri,
        private string $method,
        private mixed  $controllerAction,
        private array  $middlewares,
    )
    {
    }

    /**
     * @return array
     */
    public function getUri(): array
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string|callable
     */
    public function getControllerAction(): string|callable
    {
        return $this->controllerAction;
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middlewares;
    }
}
