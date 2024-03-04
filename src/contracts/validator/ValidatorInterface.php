<?php

namespace trinity\contracts\validator;

use trinity\validator\AbstractFormRequest;

interface ValidatorInterface
{
    public function validate(AbstractFormRequest $form): void;
}
