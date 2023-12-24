<?php

namespace trinity\validator;

use trinity\contracts\DatabaseConnectionInterface;
use trinity\contracts\RequestInterface;
use trinity\exception\baseException\ValidationError;

abstract class AbstractFormRequest
{
    private array $errors = [];
    private array $attributes;

    /**
     * @param RequestInterface $request
     * @param DatabaseConnectionInterface $connection
     */
    public function __construct(
        protected readonly RequestInterface $request,
        protected DatabaseConnectionInterface $connection,
    )
    {
        $this->attributes = array_merge(
            [
                $this->request->post() ?? [],
                $this->request->get() ?? []
            ]
        );
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $field
     * @return mixed
     * @throws ValidationError
     */
    public function getAttribute(string $field): mixed
    {
        $findAttribute = null;

        array_walk_recursive($this->attributes, function ($value, $key) use ($field, &$findAttribute) {
            if ($key === $field) {
                $findAttribute = $value;
            }
        });

        if (is_null($findAttribute) === true) {
            throw new ValidationError('Атрибута ' . $field . ' не существует');
        }

        return $findAttribute;
    }

    /**
     * @param string $field
     * @param string $message
     * @return void
     */
    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function hasAttribute(string $field): bool
    {
        return array_key_exists($field, $this->attributes);
    }

    /**
     * @return string|null
     */
    public function getErrors(): string|null
    {
        return array_shift($this->errors);
    }

    /**
     * @return array
     */
    abstract public function rules(): array;
}
