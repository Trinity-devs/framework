<?php

namespace trinity\contracts;

interface RoutesCollectionInterface
{
    public function getRoutes(): array;
    public function group(string $route, callable $callback): void;
}