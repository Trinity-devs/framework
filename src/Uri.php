<?php

namespace trinity;

use trinity\contracts\UriInterface;

class Uri implements UriInterface
{
    private array $hostAndPort;
    private string $scheme;
    private ?string $uri;
    private ?string $query;
    private string $user = 'User';
    private string $password = '123';
    private array $queryArg = [];
    private array $queryArgsName = [];

    public function __construct(array $server)
    {
        $this->hostAndPort = explode(":", $server['HTTP_HOST']);
        $this->uri = $server['REQUEST_URI'];
        $this->query = $server['QUERY_STRING'] ?? '';

        $parsed_uri = parse_url($this->uri);

        if (isset($parsed_uri['query']) === true) {
            parse_str($parsed_uri['query'], $assocParams);

            foreach ($assocParams as $argumentName => $arg) {
                $this->queryArg[] = $arg;
                $this->queryArgsName[] = $argumentName;
            }
        }
    }

    public function getQueryArgsName(): array
    {
        return $this->queryArgsName;
    }

    public function getQueryArg(): array
    {
        return $this->queryArg;
    }

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return strtolower($this->scheme);
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): string
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        $authority = '';

        if (empty($userInfo) === false) {
            $authority .= $userInfo . '@';
        }

        $authority .= $host;

        if (empty($port) === false && $this->isStandardPort() === false) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    private function isStandardPort(): bool
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if ($scheme === 'http' && $port === 80) {
            return true;
        }

        if ($scheme === 'https' && $port === 443) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
        $user = $this->getUser();
        $password = $this->getPassword();

        if ($user !== '') {
            if ($password !== '') {
                return $user . ':' . $password;
            }

            return $user;
        }

        return '';
    }

    public function __call(string $method, array $arguments = []): mixed
    {
        if ($method === 'getUser') {
            return $this->user;
        }

        if ($method === 'getPassword') {
            return $this->password;
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return strtolower($this->hostAndPort[0] ?? '');
    }

    /**
     * @inheritDoc
     */
    public function getPort(): ?int
    {
        $getPort = $this->hostAndPort[1] ?? null;

        if ($getPort !== null && $this->isStandardPort() === false) {
            return (int) $getPort;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        $path = $this->uri ?? '/';

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return $path;
    }

    public function getRoute(): string
    {
        return parse_url($this->uri)['path'];
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        if ($this->query[0] === '?') {
            $this->query = substr($this->query, 1);
        }

        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        $fragment = parse_url($this->uri, PHP_URL_FRAGMENT) ?? '';

        if (!empty($fragment)) {
            $fragment = rawurldecode($fragment);
        }

        return $fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme(string $scheme): static
    {
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo(string $user, string $password = null): static
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withHost(string $host): static
    {
        $clone = clone $this;
        $clone->hostAndPort[0] = $host;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPort(?int $port): static
    {
        $clone = clone $this;
        $clone->hostAndPort[1] = $port;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPath(string $path): static
    {
        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withQuery(string $query): static
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withFragment(string $fragment): static
    {
        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        if ($this->authority !== '') {
            $uri .= '//' . $this->authority;
        }

        $uri .= $this->path;

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}