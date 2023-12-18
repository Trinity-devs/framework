<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class StringValidator implements ValidatorInterface
{

    public function validate(string $field, array $params, Validator $validator): bool
    {
        $value = $validator->getDataValue($field);


    }
}