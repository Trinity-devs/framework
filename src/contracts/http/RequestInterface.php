<?php

namespace trinity\contracts\http;

use Psr\Http\Message\RequestInterface as BaseRequestInterface;

interface RequestInterface extends BaseRequestInterface
{
    public function post(?string $name = null): array|string|null;

    public function get(?string $name = null): array|string|null;

    public function setRequestParams(array $params): void;

    public function setIdentityParams(object $params): void;

    public function getIdentity(): object|array;

    public function getUserId(): null|int;
}
