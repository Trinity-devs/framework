<?php

namespace trinity\contracts;

use trinity\validator\AbstractFormRequest;

interface ValidatorInterface
{
    public function validate(AbstractFormRequest $form): void;
}