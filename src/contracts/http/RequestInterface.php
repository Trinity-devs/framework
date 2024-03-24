<?php

namespace trinity\contracts\http;

use JsonException;
use Psr\Http\Message\RequestInterface as BaseRequestInterface;
use Throwable;
use trinity\exception\baseException\InvalidArgumentException;
use trinity\exception\httpException\NotFoundHttpException;

interface RequestInterface extends BaseRequestInterface
{
    /**
     * Retrieves a value from the query parameters by name. If no name is provided,
     * returns all query parameters. Falls back to requestParams if the query parameter is not found.
     *
     * @param string|null $name The name of the query parameter to retrieve.
     * @return array|string The value of the query parameter, or all query parameters if no name is provided.
     */
    public function get(?string $name = null): array|string;

    /**
     * Retrieves a value from the POST data by name using a parsed body. If no name is provided,
     * returns all POST data. Throws NotFoundHttpException if the specified parameter is not found.
     *
     * @param string|null $name The name of the POST parameter to retrieve.
     * @return array|string The value of the POST parameter, or all POST data if no name is provided.
     * @throws NotFoundHttpException If the specified parameter is not found.
     * @throws JsonException If there is an error parsing JSON data.
     */
    public function post(?string $name = null): array|string;

    /**
     * Sets additional parameters for the request.
     *
     * @param array $params An array of parameters to be added to the request.
     * @return void
     */
    public function setRequestParams(array $params): void;

    /**
     * Sets the identity parameters of the request.
     *
     * @param object|array $params The identity parameters to be set.
     * @return void
     */
    public function setIdentityParams(object|array $params): void;

    /**
     * Retrieves the identity parameters of the request.
     *
     * @return object|array The identity parameters of the request.
     */
    public function getIdentity(): object|array;

    /**
     * Attempts to retrieve the user ID from the cookie data.
     *
     * @return int|null The user ID if present in the cookie data, null otherwise.
     * @throws InvalidArgumentException If an invalid argument is provided.
     * @throws Throwable If an unexpected error occurs.
     */
    public function getUserId(): null|int;
}
