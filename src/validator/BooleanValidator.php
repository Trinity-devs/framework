<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class BooleanValidator implements ValidatorInterface
{
    public function validate(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if (is_bool($value) === false) {
            $validator->addError($field, 'Значение должно быть булевым');
        }
    }
}