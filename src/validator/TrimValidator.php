<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class TrimValidator implements ValidatorInterface
{
    public function validate(string $field, array $params, Validator $validator): bool
    {
        $value = trim($validator->getDataValue($field));

        $validator->setDataValue($field, $value);

        return true;
    }
}