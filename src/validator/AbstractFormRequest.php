<?php

namespace trinity\validator;

use trinity\contracts\RequestInterface;

abstract class AbstractFormRequest
{
    private array $data;
    public function __construct(private readonly RequestInterface $request)
    {
        $this->data = $this->request->post();
    }

    public function getDataValue(string $field): string|array
    {
        return $this->data[$field];
    }

    public function setDataValue(string $field, mixed $value): void
    {
        $this->data[$field] = $value;
    }

    abstract public function rules(): array;
}
