<?php

namespace trinity\contracts\router;

interface RoutesCollectionInterface
{
    public function getRoutes(): array;
    public function group(string $route, callable $callback, array $middleware = []): void;
}
