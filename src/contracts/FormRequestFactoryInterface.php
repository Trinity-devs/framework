<?php

namespace trinity\contracts;

use trinity\validator\AbstractFormRequest;

interface FormRequestFactoryInterface
{
    public function create(string $formNameSpace): AbstractFormRequest;
}