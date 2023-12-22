<?php

namespace trinity\contracts;

interface RoutesCollectionInterface
{
    public const TYPE_RESPONSE_JSON = 'json';
    public const TYPE_RESPONSE_HTML = 'html';

    public function getRoutes(): array;
    public function group(string $route, callable $callback, string $typeResponse = self::TYPE_RESPONSE_HTML, array $middleware = []): void;
}