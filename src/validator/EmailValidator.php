<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class EmailValidator implements ValidatorInterface
{
    public function validate(string $field, array $params, Validator $validator): bool
    {
        $value = $validator->getDataValue($field);

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $validator->addError($field, 'Некорректный email');

            return false;
        }

        return true;
    }
}