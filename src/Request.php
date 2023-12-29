<?php

namespace trinity;

use trinity\contracts\http\RequestInterface;
use trinity\contracts\http\StreamInterface;
use trinity\contracts\http\UriInterface;
use trinity\exception\{baseException\Exception, httpException\NotFoundHttpException};

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
    private array $requestParams = [];
    private object|array $identity = [];

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
     * @return array|string
     */
    public function get(string|null $name = null): array|string
    {
        if ($name === null) {
            return $this->queryParams === [] ? $this->requestParams : $this->queryParams;
        }

        return $this->queryParams[$name] ?? $this->requestParams[$name];
    }

    /**
     * @param string|null $name
     * @return array|string
     * @throws NotFoundHttpException
     */
    public function post(string|null $name = null): array|string
    {
        return $this->getParsedBody($name);
    }

    /**
     * @param string|null $name
     * @return array|string
     * @throws NotFoundHttpException
     */
    private function getParsedBody(string|null $name = null): array|string
    {
        if ($this->contentType === 'application/x-www-form-urlencoded') {
            parse_str(file_get_contents('php://input'), $this->input);
        }

        if ($this->contentType === 'application/json') {
            $this->input = json_decode(file_get_contents('php://input'), true);
        }

        if ($name === null) {
            return $this->input !== [] ? $this->input : [];
        }

        if (array_key_exists($name, $this->input) === false) {
            throw new NotFoundHttpException("Параметр $name не найден");
        }

        return $this->input !== [] ? $this->input[$name] : [];
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

    /**
     * @param array $params
     * @return void
     */
    public function setRequestParams(array $params): void
    {
        $this->requestParams = $params;
    }

    /**
     * @param object $params (param DTO object)
     * @return void
     */
    public function setIdentityParams(object $params): void
    {
        $this->identity = $params;
    }

    /**
     * @return object|array
     */
    public function identity(): object|array
    {
        return $this->identity;
    }
}
