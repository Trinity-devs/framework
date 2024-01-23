<?php

namespace trinity\validator;

use trinity\contracts\{database\DatabaseConnectionInterface, http\RequestInterface};
use trinity\exception\baseException\ValidationError;

abstract class AbstractFormRequest
{
    private array $errors = [];
    private array $attributes;
    private bool $skipOnEmptyMode = false;

    /**
     * @param RequestInterface $request
     * @param DatabaseConnectionInterface $connection
     */
    public function __construct(
        protected readonly RequestInterface   $request,
        protected DatabaseConnectionInterface $connection,
    )
    {
        $this->attributes = array_merge(
            [
                $this->request->post(),
                $this->request->get()
            ]
        );
    }

    /**
     * @param $field
     * @param $newValue
     * @return void
     */
    public function setAttribute($field, $newValue): void
    {
        $this->setAttributeRecursive($field, $newValue, $this->attributes);
    }

    /**
     * @param $field
     * @param $newValue
     * @param $attributes
     * @return void
     */
    private function setAttributeRecursive($field, $newValue, &$attributes = null)
    {
        foreach ($attributes as $key => &$value) {
            if ($key === $field) {
                $value = $newValue;

                return;
            }

            if (is_array($value) === true) {
                $this->setAttributeRecursive($field, $newValue, $value);
            }
        }
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
        $result = $this->getAttributeRecursive($this->attributes, $field);

        if ($result === null) {
            throw new ValidationError('Поле ' . $field . ' не ожидается');
        }

        return $result;
    }

    /**
     * @param array $attributes
     * @param string $field
     * @return mixed
     */
    private function getAttributeRecursive(array $attributes, string $field): mixed
    {
        foreach ($attributes as $key => $value) {
            if ($key === $field) {
                return $value;
            }

            if (is_array($value) === true) {
                $result = $this->getAttributeRecursive($value, $field);

                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
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
        if ($this->errors === []) {
            return null;
        }

        return array_values($this->errors)[0];
    }

    /**
     * @return void
     */
    public function setSkipEmptyValues(): void
    {
        $this->skipOnEmptyMode = true;
    }

    /**
     * @return bool
     */
    public function getSkipOnEmptyMode(): bool
    {
        return $this->skipOnEmptyMode;
    }

    /**
     * @return array
     */
    abstract public function rules(): array;
}
