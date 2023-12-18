<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class BooleanValidator implements ValidatorInterface
{
    public function validate(string $field, array $params, Validator $validator): bool
    {
        $value = $validator->getDataValue($field);

        if (is_bool($value) === false) {
            $validator->addError($field, 'Значение должно быть булевым');

            return false;
        }

        return true;
    }
}