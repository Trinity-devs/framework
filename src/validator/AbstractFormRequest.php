<?php

namespace trinity\validator;

use trinity\contracts\database\DatabaseConnectionInterface;
use trinity\contracts\http\RequestInterface;
use trinity\exception\baseException\InvalidArgumentException;
use trinity\helpers\ArrayHelper;

abstract class AbstractFormRequest
{
    private array $errors = [];
    private array $attributes;
    private bool $skipOnEmptyMode = false;
    protected string|null $attributesLabel = null;
    protected bool $validateGetParams = false;

    /**
     * @param RequestInterface $request
     * @param DatabaseConnectionInterface $db
     */
    public function __construct(
        protected RequestInterface            $request,
        protected DatabaseConnectionInterface $db,
    )
    {
        $this->attributes = $this->request->post();

        if (empty($this->attributesLabel) === false) {
            $this->putAttributesInLabel();
        }

        if ($this->validateGetParams === true) {
            $this->attributes = array_merge($this->attributes, $this->request->get());
        }
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
    private function setAttributeRecursive($field, $newValue, &$attributes = null): void
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
     */
    public function getAttribute(string $field): mixed
    {
        return $this->getAttributeRecursive($this->attributes, $field);
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
     * @throws InvalidArgumentException
     */
    public function hasAttribute(string $field): bool
    {
        return ArrayHelper::keyExists($field, $this->getAttributes());
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
     * @return void
     */
    private function putAttributesInLabel(): void
    {
        if ($this->getAttribute($this->attributesLabel) === null) {
            $this->attributes = [];

            return;
        }
        
        $this->attributes = $this->getAttribute($this->attributesLabel);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteAttributes(array $fields): void
    {
        foreach ($fields as $key => $value) {
            if (ArrayHelper::keyExists($key, $this->attributes) === true) {
                unset($this->attributes[$key]);
            }
        }
    }

    /**
     * @return array
     */
    abstract public function rules(): array;
}
