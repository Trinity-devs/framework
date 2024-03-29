<?php

declare(strict_types=1);

namespace trinity\http;

use GuzzleHttp\Psr7\Request as BaseRequest;
use JsonException;
use Throwable;
use trinity\contracts\http\RequestInterface;
use trinity\exception\baseException\InvalidArgumentException;
use trinity\exception\httpException\NotFoundHttpException;
use trinity\helpers\ArrayHelper;

class Request extends BaseRequest implements RequestInterface
{
    private string $contentType;
    private array $queryParams;
    private array $input;
    private array $requestParams = [];
    private object|null $identity = null;
    private array $cookie;

    /**
     * @param array $server
     * @param array $get
     * @param array $post
     * @param array $cookie
     */
    public function __construct(array $server, array $get, array $post, array $cookie)
    {
        parent::__construct($server['REQUEST_METHOD'], $server['REQUEST_URI'], getallheaders());
        $this->queryParams = $get;
        $this->contentType = $_SERVER['CONTENT_TYPE'] ?? 'application/json';
        $this->input = $post;
        $this->cookie = $cookie;
    }

    /**
     * @param string|null $name
     * @return array|string
     */
    public function get(?string $name = null): array|string
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
     * @throws JsonException
     */
    public function post(?string $name = null): array|string
    {
        return $this->getParsedBody($name);
    }

    /**
     * @param string|null $name
     * @return array|string
     * @throws NotFoundHttpException
     * @throws JsonException
     */
    private function getParsedBody(string|null $name = null): array|string
    {
        if ($this->contentType === 'application/x-www-form-urlencoded') {
            parse_str(file_get_contents('php://input'), $this->input);
        }

        if ($this->contentType === 'application/json') {
            $this->input = json_decode
            (
                file_get_contents('php://input'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        if ($name === null) {
            return $this->input;
        }

        if (array_key_exists($name, $this->input) === false) {
            throw new NotFoundHttpException("Параметр $name не найден");
        }

        return $this->input !== [] ? $this->input[$name] : [];
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
     * @param object|array $params (param DTO object)
     * @return void
     */
    public function setIdentityParams(object|array $params): void
    {
        if ($this->identity === null) {
            $this->identity = $params;

            return;
        }

        throw new InvalidArgumentException('Identity инициализирован другим обьектом: ' . get_class($this->identity));
    }

    /**
     * @return object
     */
    public function getIdentity(): object
    {
        if ($this->identity === null) {
            throw new InvalidArgumentException('Identity не инициализирован');
        }

        return $this->identity;
    }

    /**
     * @return int|null
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function getUserId(): null|int
    {
        if (ArrayHelper::keyExists('userId', $this->cookie)) {
            return (int)ArrayHelper::getValue($this->cookie, 'userId');
        }

        return null;
    }

    public function getAccessToken(): null|string
    {
        if (ArrayHelper::keyExists('access_token', $this->cookie)) {
            return ArrayHelper::getValue($this->cookie, 'access_token');
        }

        return null;
    }
}
