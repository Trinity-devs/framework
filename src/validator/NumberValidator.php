<?php

namespace trinity\validator;

use trinity\contracts\ValidatorInterface;

class NumberValidator implements ValidatorInterface
{
    private $integerPattern = '/^[0-9]+$/';

    public function validate(string $field, array $params, Validator $validator): bool
    {
        $value = $validator->getDataValue($field);

        if (preg_match($this->integerPattern, $value) === 0) {
            $validator->addError($field, 'Значение должно быть целым числом');

            return false;
        }

        return true;
    }
}