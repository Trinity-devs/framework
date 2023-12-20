<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class DateValidator implements ValidatorInterface
{

    public function validate(string $field, array $params, Validator $validator): void
    {
        $value = $validator->getDataValue($field);

        if (strtotime($value) === false) {
            $validator->addError($field, 'Значение должно быть датой');
        }
    }
}