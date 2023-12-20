<?php

namespace trinity\validator;

use trinity\contracts\RequestInterface;

abstract class AbstractFormRequest
{
    private array $data = [];

    public function __construct(private readonly RequestInterface $request)
    {
        $this->data = $this->request->post();
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    abstract public function rules(): array;
}
