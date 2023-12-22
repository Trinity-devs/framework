<?php

namespace trinity\router;

class Route
{
    /**
     * @param array $url
     * @param string $method
     * @param mixed $controllerAction
     * @param string $typeResponse
     * @param array $middlewares
     */
    public function __construct(
        private array  $url,
        private string $method,
        private mixed  $controllerAction,
        private string $typeResponse,
        private array  $middlewares,
    )
    {
    }

    /**
     * @return array
     */
    public function getUrl(): array
    {
        return $this->url;
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

    /**
     * @return string
     */
    public function getTypeResponse(): string
    {
        return $this->typeResponse;
    }
}
