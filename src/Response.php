<?php

namespace trinity;

use trinity\{contracts\http\ResponseInterface,
    exception\baseException\InvalidArgumentException,
    exception\httpException\HttpException};
use Throwable;

class Response implements ResponseInterface
{
    public static array $httpStatuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    private mixed $body = '';
    private string $statusCode = '200';
    private string $reasonPhrase = 'OK';
    private string $protocolVersion = 'HTTP/1.1';
    private array $headers = [];

    public function send(): void
    {
        header("{$this->protocolVersion} {$this->statusCode} {$this->reasonPhrase}\r\n");
        foreach ($this->getHeaders() as $name => $value) {
            header($this->getHeaderLine($name));
        }

        echo $this->getBody();
    }
    
    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    public function getHeaderLine($name): string
    {
        if (isset($this->headers[$name])) {
            return "{$name}: {$this->headers[$name]}\r\n";
        }

        return '';
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function getProtocolVersion(): string
    {
        if (isset($this->protocolVersion) === true) {
            return $this->protocolVersion;
        }

        return '';
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader($name): array
    {
        return $this->headers[$name];
    }

    public function hasHeader($name): bool
    {
    }

    public function withProtocolVersion($version): static
    {
        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    public function withHeader($name, $value): static
    {
        $new = clone $this;
        $new->headers[$name] = $value;

        return $new;
    }

    public function withAddedHeader($name, $value): static
    {
    }

    public function withoutHeader($name): static
    {
    }


    public function withBody(mixed $body): static
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }


    public function withStatus($code, $reasonPhrase = ''): static
    {
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }


    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    private function setStatusCode(int $value, string|null $text = null): Response
    {
        $this->statusCode = $value;

        if ($this->getIsInvalid()) {
            throw new InvalidArgumentException("Код состояния HTTP недействителен: $value");
        }

        if ($text === null) {
            $this->reasonPhrase = static::$httpStatuses[$this->statusCode] ?? '';
        }

        if ($text !== null) {
            $this->reasonPhrase = $text;
        }

        return $this;
    }

    public function getIsInvalid(): bool
    {
        return $this->getStatusCode() < 100 || $this->getStatusCode() >= 600;
    }

    public function setStatusCodeByException(Throwable $e): Response
    {
        $new = clone $this;

        if ($e instanceof HttpException) {
            $new->setStatusCode($e->statusCode);

            return $new;
        }

        $new->setStatusCode(500);

        return $new;
    }
}