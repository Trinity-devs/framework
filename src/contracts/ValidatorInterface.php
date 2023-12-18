<?php

namespace trinity\contracts;

use trinity\validator\Validator;

interface ValidatorInterface
{
    public function validate(string $field, array $params, Validator $validator): bool;
}