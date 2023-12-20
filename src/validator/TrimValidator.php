<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class TrimValidator implements ValidatorInterface
{
    public function validate(string $field, array $params, Validator $validator): void
    {
        $value = trim($validator->getDataValue($field));

        $validator->setDataValue($field, $value);
        
        if (is_string($value) === false) {
            $validator->addError($field, 'Значение должно быть строкой');
        }
    }
}