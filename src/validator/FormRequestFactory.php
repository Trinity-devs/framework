<?php

namespace trinity\validator;

use trinity\contracts\FormRequestFactoryInterface;
use trinity\validator\AbstractFormRequest;

class FormRequestFactory implements FormRequestFactoryInterface
{
    public function __construct(private $container)
    {
    }

    public function create(string $formNameSpace): AbstractFormRequest
    {
        return $this->container->get($formNameSpace);
    }
}