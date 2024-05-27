<?php

declare(strict_types=1);

namespace trinity\http;

use GuzzleHttp\Psr7\Request as BaseRequest;
use JsonException;
use Throwable;
use trinity\contracts\entity\EntityInterface;
use trinity\contracts\http\RequestInterface;
use trinity\exception\baseException\Exception;
use trinity\exception\baseException\InvalidArgumentException;
use trinity\exception\httpException\NotFoundHttpException;
use trinity\helpers\ArrayHelper;

class Request extends BaseRequest implements RequestInterface
{
    private string $contentType;
    private array $queryParams;
    private array $input;
    private array $requestParams = [];
    private EntityInterface|null $identity = null;
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
        if (empty($this->input) === false) {
            if ($name === null) {
                return $this->input;
            }

            if (array_key_exists($name, $this->input) === false) {
                throw new NotFoundHttpException("Параметр $name не найден");
            }

            return $this->input[$name];
        }

        $post = [];
        if ($this->contentType === 'application/x-www-form-urlencoded') {
            parse_str(file_get_contents('php://input'), $post);
        }

        if ($this->contentType === 'application/json') {
            $post = json_decode
            (
                file_get_contents('php://input'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        if ($name === null) {
            return $post;
        }

        if (array_key_exists($name, $post) === false) {
            throw new NotFoundHttpException("Параметр $name не найден");
        }

        return $post !== [] ? $post[$name] : [];
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
     * @param EntityInterface|null $params (param DTO object)
     * @return void
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function setIdentityParams(EntityInterface|null $params): void
    {
        if ($params === null) {
            throw new Exception('Нельзя создать UserEntity с пустыми параметрами');
        }

        if ($this->identity === null) {
            $this->identity = $params;

            return;
        }

        throw new InvalidArgumentException('Identity инициализирован другим обьектом: ' . get_class($this->identity));
    }

    /**
     * @return EntityInterface
     */
    public function getIdentity(): EntityInterface
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
        return $this->getCookie('userId');
    }

    public function getAccessToken(): null|string
    {
       return $this->getCookie('access_token');
    }

    public function getCookie(string $name): null|string|int
    {
        if (ArrayHelper::keyExists($name, $this->cookie)) {
            $value = ArrayHelper::getValue($this->cookie, $name);

            if (is_numeric($value)) {
                return (int)$value;
            }

            return $value;
        }

        return null;
    }
}
