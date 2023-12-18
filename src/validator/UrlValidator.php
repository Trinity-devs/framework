<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class UrlValidator implements ValidatorInterface
{

    public function validate(string $field, array $params, Validator $validator): bool
    {
        $value = $validator->getDataValue($field);

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $validator->addError($field, 'Значение должно быть URL');

            return false;
        }

        return true;
    }
}