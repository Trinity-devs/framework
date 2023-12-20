<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class StringValidator implements ValidatorInterface
{

    public function validate(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if (is_string($value) === false) {
            $validator->addError($field, 'Значение должно быть строкой');
        }
    }
}