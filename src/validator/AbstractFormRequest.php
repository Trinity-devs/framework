<?php

namespace trinity\validator;

use trinity\contracts\RequestInterface;
use trinity\exception\baseException\ValidationError;

abstract class AbstractFormRequest
{
    private array $errors = [];
    private array $attributes;

    /**
     * @param RequestInterface $request
     */
    public function __construct(private readonly RequestInterface $request)
    {
        $this->attributes = $this->request->post() ?? [];
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
        if ($this->hasAttribute($field) === true) {
            return $this->attributes[$field];
        }

        throw new ValidationError('Атрибута ' . $field . ' не существует');
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
     * @return string
     */
    public function getErrors(): string
    {
        $answer = '';

        foreach ($this->errors as $key => $value) {
            $answer .= $key . ' - ' . $value . PHP_EOL;
        }

        return $answer;
    }

    /**
     * @return array
     */
    abstract public function rules(): array;
}
