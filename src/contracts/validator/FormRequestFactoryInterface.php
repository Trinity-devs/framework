<?php

namespace trinity\contracts\validator;

use trinity\validator\AbstractFormRequest;

interface FormRequestFactoryInterface
{
    public function create(string $formNameSpace): AbstractFormRequest;
}