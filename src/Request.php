<?php

namespace trinity;

use trinity\contracts\RequestInterface;
use trinity\contracts\StreamInterface;
use trinity\contracts\UriInterface;
use trinity\exception\baseException\Exception;
use trinity\exception\httpException\NotFoundHttpException;

class Request implements RequestInterface
{
    private array $queryArg = [];
    private array $queryArgsName = [];
    private string $path;
    private string $protocolVersion;
    private string $method;
    private string $contentType;
    private $headers;
    private $uri;
    private mixed $requestTarget;
    private array $queryParams;
    private array $input;

    public function __construct(array $server, array $get, array $post)
    {
        $this->protocolVersion = $server['SERVER_PROTOCOL'];
        $this->method = $server['REQUEST_METHOD'];
        $this->queryParams = $get;
        $this->contentType = $server['CONTENT_TYPE'];
        $this->input = $post;
        $this->headers = getallheaders();
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version): static
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * @param string|null $name
     * @return array|string|null
     */
    public function get(string|null $name = null): array|string|null
    {
        if ($name === null) {
            return $this->queryParams !== [] ? $this->queryParams : null;
        }

        return $this->queryParams !== [] ? $this->queryParams[$name] : null;
    }

    public function post(string|null $name = null): array|string|null
    {
        if ($name === null) {
            return $this->getParsedBody();
        }

        return $this->getParsedBody($name);
    }

    private function getParsedBody(string|null $name = null): array|string|null
    {
        if ($name !== null && $this->contentType === 'application/x-www-form-urlencoded') {
            parse_str(file_get_contents('php://input'), $this->input);
        }

        if ($name !== null && $this->contentType === 'application/json') {
            $this->input = json_decode(file_get_contents('php://input'), true);
        }

        if ($name === null) {
            return $this->input !== [] ? $this->input : null;
        }

        if (array_key_exists($name, $this->input) === false) {
            throw new NotFoundHttpException("Параметр $name не найден");
        }

        return $this->input !== [] ? $this->input[$name] : null;
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        if (isset($this->headers[$name]) === true) {
            return $this->headers[$name];
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        $headers = $this->getHeaders();

        if (isset($headers[$name]) === false) {
            return '';
        }

        $values = $headers[$name];

        return implode(', ', $values);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value): static
    {
        $headers = $this->getHeaders();

        $lowercaseName = strtolower($name);

        $headers[$lowercaseName] = $value;

        $newRequest = clone $this;
        $newRequest->headers = $headers;

        return $newRequest;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value): static
    {
        $new = clone $this;

        if ($new->hasHeader($name) === true) {
            $currentValues = $new->getHeader($name);

            $new->headers[$name] = array_merge($currentValues, (array)$value);

            return $new;
        }

        $new->headers[$name] = (array)$value;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name): static
    {
        $name = strtolower($name);

        if (!isset($this->headers[$name])) {
            return $this;
        }

        $newHeaders = $this->headers;
        unset($newHeaders[$name]);

        $newInstance = clone $this;
        $newInstance->headers = $newHeaders;

        return $newInstance;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        // TODO: Implement getBody() method.
    }

    /**
     * @inheritDoc
     */
    public function withBody($body)
    {
        // TODO: Implement withBody() method.
    }

    /**
     * @inheritDoc
     */
    public function getRequestTarget(): string
    {
        if ($this->uri !== null) {
            $path = $this->uri->getPath();
            $query = $this->uri->getQuery();

            return ($path !== '' ? $path : '/') . ($query !== '' ? '?' . $query : '');
        }

        return '/';
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget): static
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method): static
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        // TODO: Implement getUri() method.
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        // TODO: Implement withUri() method.
    }
}
